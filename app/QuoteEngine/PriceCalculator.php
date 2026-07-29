<?php

namespace App\QuoteEngine;

use App\Enums\InputType;
use App\Enums\ModifierType;
use App\Enums\PricingStrategy;
use App\Models\CatalogProduct;
use App\Models\Component;
use App\Models\OptionPriceModifier;
use App\Models\PricingProfile;
use App\Models\ProductTemplate;
use App\QuoteEngine\Exceptions\QuoteCannotBeCalculatedException;
use Illuminate\Support\Collection;

/**
 * Turns a customer's selections on a CatalogProduct into a real price.
 *
 * Components are matched to their role by a fixed code convention rather
 * than a dedicated schema flag: the component driving a PER_UNIT_TIERED
 * quote must be coded "quantity"; the one driving a PER_AREA(_WITH_SETUP)
 * quote must be coded "dimensions" and carry a {width, height} value. Any
 * other CHOICE component only ever affects price through an
 * OptionPriceModifier.
 */
final class PriceCalculator
{
    /**
     * @param  array<string, mixed>  $selections  keyed by Component code
     */
    public function calculate(CatalogProduct $catalogProduct, array $selections): QuoteResult
    {
        $catalogProduct->loadMissing([
            'shop',
            'productTemplate.components.options',
            'pricingProfile.tiers',
            'pricingProfile.optionModifiers',
        ]);

        $template = $catalogProduct->productTemplate;
        $profile = $catalogProduct->pricingProfile;

        if ($profile === null) {
            throw QuoteCannotBeCalculatedException::missingPricingProfile($catalogProduct);
        }

        $this->validateSelections($template, $selections);

        [$basePrice, $quantity] = match ($template->pricing_strategy) {
            PricingStrategy::PerUnitTiered => $this->calculateTiered($profile, $selections),
            PricingStrategy::PerArea => $this->calculateArea($profile, $selections, withSetup: false),
            PricingStrategy::PerAreaWithSetup => $this->calculateArea($profile, $selections, withSetup: true),
        };

        $selectedOptionLabels = $this->resolveSelectedOptionLabels($template, $selections);

        $applicableModifiers = $profile->optionModifiers
            ->filter(fn (OptionPriceModifier $modifier) => $selectedOptionLabels->has($modifier->component_option_id));

        [$modifiers, $total] = $this->applyModifiers($basePrice, $quantity, $applicableModifiers, $selectedOptionLabels);

        return new QuoteResult(
            basePrice: round($basePrice, 2),
            modifiers: $modifiers,
            total: round($total, 2),
            currency: $catalogProduct->shop->currency,
        );
    }

    /**
     * @param  array<string, mixed>  $selections
     */
    private function validateSelections(ProductTemplate $template, array $selections): void
    {
        foreach ($template->components as $component) {
            $value = $selections[$component->code] ?? null;

            if ($value === null) {
                if ($component->pivot->is_required) {
                    throw QuoteCannotBeCalculatedException::missingSelection($component->code);
                }

                continue;
            }

            $valid = match ($component->input_type) {
                InputType::Choice => is_string($value) && $component->options->contains('value', $value),
                InputType::Number => is_numeric($value),
                InputType::Dimensions => $this->isValidDimensions($value),
            };

            if (! $valid) {
                throw QuoteCannotBeCalculatedException::invalidSelection($component->code, $value);
            }
        }
    }

    private function isValidDimensions(mixed $value): bool
    {
        return is_array($value)
            && isset($value['width'], $value['height'])
            && is_numeric($value['width'])
            && is_numeric($value['height'])
            && (float) $value['width'] > 0
            && (float) $value['height'] > 0;
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return array{0: float, 1: int}
     */
    private function calculateTiered(PricingProfile $profile, array $selections): array
    {
        $rawQuantity = $selections['quantity'] ?? null;

        if (! is_numeric($rawQuantity)) {
            throw QuoteCannotBeCalculatedException::missingSelection('quantity');
        }

        $quantity = (int) $rawQuantity;

        $tier = $profile->tiers->first(
            fn ($tier) => $quantity >= $tier->min_quantity
                && ($tier->max_quantity === null || $quantity <= $tier->max_quantity),
        );

        if ($tier === null) {
            throw QuoteCannotBeCalculatedException::noTierForQuantity($quantity);
        }

        return [$tier->effectiveUnitPrice() * $quantity, $quantity];
    }

    /**
     * Previews every PricingTier's price with whatever option modifiers
     * are currently selected, so the customer-facing quantity table can
     * update live as they pick other options - before they've picked a
     * quantity, and even before every required field is filled in. Unlike
     * calculate(), this never throws: it's a preview, not a final quote.
     *
     * @param  array<string, mixed>  $selections  keyed by Component code
     * @return list<array{min_quantity: int, max_quantity: int|null, unit_price: float, total: float}>
     */
    public function calculateTierTable(CatalogProduct $catalogProduct, array $selections): array
    {
        $catalogProduct->loadMissing([
            'productTemplate.components.options',
            'pricingProfile.tiers',
            'pricingProfile.optionModifiers',
        ]);

        $template = $catalogProduct->productTemplate;
        $profile = $catalogProduct->pricingProfile;

        if ($profile === null || $template->pricing_strategy !== PricingStrategy::PerUnitTiered) {
            return [];
        }

        $selectedOptionLabels = $this->resolveSelectedOptionLabels($template, $selections);

        $applicableModifiers = $profile->optionModifiers
            ->filter(fn (OptionPriceModifier $modifier) => $selectedOptionLabels->has($modifier->component_option_id));

        $rows = [];

        foreach ($profile->tiers as $tier) {
            $basePrice = $tier->effectiveUnitPrice() * $tier->min_quantity;

            [, $total] = $this->applyModifiers($basePrice, $tier->min_quantity, $applicableModifiers, $selectedOptionLabels);

            $rows[] = [
                'min_quantity' => $tier->min_quantity,
                'max_quantity' => $tier->max_quantity,
                'unit_price' => round($total / $tier->min_quantity, 2),
                'total' => round($total, 2),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return array{0: float, 1: int}
     */
    private function calculateArea(PricingProfile $profile, array $selections, bool $withSetup): array
    {
        $dimensions = $selections['dimensions'] ?? null;

        if (! $this->isValidDimensions($dimensions)) {
            throw QuoteCannotBeCalculatedException::missingSelection('dimensions');
        }

        $ratePerSqm = $profile->params['rate_per_sqm'] ?? null;

        if (! is_numeric($ratePerSqm)) {
            throw QuoteCannotBeCalculatedException::missingPricingParam('rate_per_sqm');
        }

        $area = (float) $dimensions['width'] * (float) $dimensions['height'];
        $price = $area * (float) $ratePerSqm;

        if ($withSetup) {
            $setupFee = $profile->params['setup_fee'] ?? null;

            if (! is_numeric($setupFee)) {
                throw QuoteCannotBeCalculatedException::missingPricingParam('setup_fee');
            }

            $price += (float) $setupFee;
        }

        return [$price, 1];
    }

    /**
     * Maps selected ComponentOption ids to a display label, for every
     * CHOICE component the customer picked a value for.
     *
     * @param  array<string, mixed>  $selections
     * @return Collection<int, string>
     */
    private function resolveSelectedOptionLabels(ProductTemplate $template, array $selections): Collection
    {
        $labels = collect();

        foreach ($template->components as $component) {
            if ($component->input_type !== InputType::Choice) {
                continue;
            }

            $value = $selections[$component->code] ?? null;

            if ($value === null) {
                continue;
            }

            $option = $component->options->firstWhere('value', $value);

            if ($option !== null) {
                $labels->put($option->id, "{$component->label}: {$option->label}");
            }
        }

        return $labels;
    }

    /**
     * Additive modifiers (FIXED_ADD, PER_UNIT_ADD) are applied first, in
     * their own order, against the base price. PERCENT_MULTIPLY modifiers
     * are applied second and compound on top of that adjusted total - a
     * "rush order" surcharge is meant to apply to the price the customer
     * would otherwise pay, extras included.
     *
     * @param  Collection<int, OptionPriceModifier>  $modifiers
     * @param  Collection<int, string>  $selectedOptionLabels
     * @return array{0: list<AppliedModifier>, 1: float}
     */
    private function applyModifiers(float $basePrice, int $quantity, Collection $modifiers, Collection $selectedOptionLabels): array
    {
        $applied = [];
        $runningTotal = $basePrice;

        foreach ($modifiers as $modifier) {
            if ($modifier->modifier_type === ModifierType::PercentMultiply) {
                continue;
            }

            $amount = $modifier->modifier_type === ModifierType::PerUnitAdd
                ? (float) $modifier->value * $quantity
                : (float) $modifier->value;

            $runningTotal += $amount;

            $applied[] = new AppliedModifier(
                label: $selectedOptionLabels->get($modifier->component_option_id, ''),
                type: $modifier->modifier_type,
                amount: round($amount, 2),
            );
        }

        foreach ($modifiers as $modifier) {
            if ($modifier->modifier_type !== ModifierType::PercentMultiply) {
                continue;
            }

            $amount = round($runningTotal * (float) $modifier->value, 2);
            $runningTotal += $amount;

            $applied[] = new AppliedModifier(
                label: $selectedOptionLabels->get($modifier->component_option_id, ''),
                type: $modifier->modifier_type,
                amount: $amount,
            );
        }

        return [$applied, $runningTotal];
    }
}
