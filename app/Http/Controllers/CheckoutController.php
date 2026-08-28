<?php

namespace App\Http\Controllers;

use App\Enums\InputType;
use App\Models\CatalogProduct;
use App\Models\Component;
use App\Models\ProductTemplate;
use App\Models\Shop;
use App\QuoteEngine\Exceptions\QuoteCannotBeCalculatedException;
use App\QuoteEngine\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Request $request, CatalogProduct $catalogProduct, PriceCalculator $calculator): Response|RedirectResponse
    {
        abort_unless(
            $catalogProduct->is_active && $catalogProduct->shop_id === Shop::current()->id,
            404,
        );

        $selections = $request->query('selections', []);

        if (! is_array($selections)) {
            $selections = [];
        }

        try {
            $quote = $calculator->calculate($catalogProduct, $selections);
        } catch (QuoteCannotBeCalculatedException) {
            return to_route('catalog.show', $catalogProduct->slug);
        }

        $catalogProduct->loadMissing('productTemplate.components.options', 'shop');

        return Inertia::render('checkout/show', [
            'catalogProduct' => [
                'slug' => $catalogProduct->slug,
                'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                'image_url' => $catalogProduct->image_url,
            ],
            'selections' => $selections,
            'selectionSummary' => $this->describeSelections($catalogProduct->productTemplate, $selections),
            'quote' => [
                'total' => $quote->total,
                'currency' => $quote->currency,
            ],
            'shop' => [
                'pickup_line1' => $catalogProduct->shop->pickup_line1,
                'pickup_city' => $catalogProduct->shop->pickup_city,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return list<string>
     */
    private function describeSelections(ProductTemplate $template, array $selections): array
    {
        $lines = [];

        if (isset($selections['quantity']) && is_numeric($selections['quantity'])) {
            $lines[] = sprintf('Cantidad: %d unidades', (int) $selections['quantity']);
        }

        /** @var Collection<int, Component> $components */
        $components = $template->components;

        foreach ($components as $component) {
            $value = $selections[$component->code] ?? null;

            if ($value === null) {
                continue;
            }

            $line = match ($component->input_type) {
                InputType::Choice => is_string($value)
                    ? $this->describeChoice($component, $value)
                    : null,
                InputType::Number => is_numeric($value) ? "{$component->label}: {$value}" : null,
                InputType::Dimensions => $this->describeDimensions($component, $value),
            };

            if ($line !== null) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    private function describeChoice(Component $component, string $value): ?string
    {
        $option = $component->options->firstWhere('value', $value);

        return $option ? "{$component->label}: {$option->label}" : null;
    }

    private function describeDimensions(Component $component, mixed $value): ?string
    {
        if (! is_array($value) || ! isset($value['width'], $value['height']) || ! is_numeric($value['width']) || ! is_numeric($value['height'])) {
            return null;
        }

        return sprintf('%s: %sm x %sm', $component->label, $value['width'], $value['height']);
    }
}
