<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductionStage;
use App\Exceptions\QualityCheckRequiredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderNoteRequest;
use App\Http\Requests\Admin\UpdateOrderProductionStageRequest;
use App\Http\Requests\Admin\UpdateOrderQualityCheckRequest;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $shop = Shop::current();

        return Inertia::render('admin/orders/index', [
            'orders' => $shop->orders()
                ->with([
                    'customer',
                    'items.catalogProduct.productTemplate',
                    'qualityCheckedByUser',
                    'stageChanges' => fn ($query) => $query->latest('id')->with('changedByUser')->limit(1),
                ])
                ->latest()
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'customer_name' => $order->customer->name,
                    'customer_email' => $order->customer->email,
                    'product_names' => $order->items
                        ->map(fn ($item) => $item->catalogProduct->name_override ?? $item->catalogProduct->productTemplate->name)
                        ->implode(', '),
                    'total' => (float) $order->total,
                    'currency' => $order->currency,
                    'status' => $order->status->value,
                    'delivery_type' => $order->delivery_type->value,
                    'production_stage' => $order->production_stage?->value,
                    'production_stage_updated_by' => $order->stageChanges->first()?->changedByUser?->name,
                    'production_stage_updated_at' => $order->stageChanges->first()?->created_at?->toIso8601String(),
                    'quality_checked' => $order->quality_checked_at !== null,
                    'quality_checked_by' => $order->qualityCheckedByUser?->name,
                    'quality_checked_at' => $order->quality_checked_at?->toIso8601String(),
                    'created_at' => $order->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function show(Order $order): Response
    {
        $this->ensureBelongsToCurrentShop($order);

        $order->load([
            'customer',
            'items.catalogProduct.productTemplate',
            'shippingAddress',
            'payments',
            'qualityCheckedByUser',
            'stageChanges' => fn ($query) => $query->orderBy('id')->with('changedByUser'),
            'notes' => fn ($query) => $query->orderBy('id')->with('user'),
        ]);

        return Inertia::render('admin/orders/show', [
            'order' => [
                'id' => $order->id,
                'customer_name' => $order->customer->name,
                'customer_email' => $order->customer->email,
                'customer_phone' => $order->customer->phone,
                'product_names' => $order->items
                    ->map(fn ($item) => $item->catalogProduct->name_override ?? $item->catalogProduct->productTemplate->name)
                    ->implode(', '),
                'total' => (float) $order->total,
                'currency' => $order->currency,
                'status' => $order->status->value,
                'delivery_type' => $order->delivery_type->value,
                'shipping_address' => $order->shippingAddress ? [
                    'recipient_name' => $order->shippingAddress->recipient_name,
                    'phone' => $order->shippingAddress->phone,
                    'line1' => $order->shippingAddress->line1,
                    'line2' => $order->shippingAddress->line2,
                    'city' => $order->shippingAddress->city,
                    'state' => $order->shippingAddress->state,
                    'postal_code' => $order->shippingAddress->postal_code,
                ] : null,
                'payment_method' => $order->payments->first()?->method?->value,
                'production_stage' => $order->production_stage?->value,
                'quality_checked' => $order->quality_checked_at !== null,
                'quality_checked_by' => $order->qualityCheckedByUser?->name,
                'quality_checked_at' => $order->quality_checked_at?->toIso8601String(),
                'created_at' => $order->created_at?->toIso8601String(),
            ],
            'events' => $this->buildTimeline($order),
        ]);
    }

    public function storeNote(StoreOrderNoteRequest $request, Order $order): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($order);

        $order->notes()->create([
            'body' => $request->validated('body'),
            'user_id' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Nota agregada.')]);

        return back();
    }

    public function updateProductionStage(UpdateOrderProductionStageRequest $request, Order $order): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($order);

        try {
            $order->advanceProductionStage(
                $request->enum('production_stage', ProductionStage::class),
                $request->user()->id,
            );
        } catch (QualityCheckRequiredException $exception) {
            throw ValidationException::withMessages(['production_stage' => $exception->getMessage()]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Etapa de produccion actualizada.')]);

        return back();
    }

    public function updateQualityCheck(UpdateOrderQualityCheckRequest $request, Order $order): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($order);

        $order->setQualityChecked($request->boolean('passed'), $request->user()->id);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $request->boolean('passed')
                ? __('Control de calidad aprobado.')
                : __('Control de calidad revertido.'),
        ]);

        return back();
    }

    /**
     * Merges stage changes, the quality-check sign-off, and free-text
     * notes into one chronological bitacora, newest first. Quality check
     * only ever contributes one entry - un-checking isn't logged, the
     * same "current value, not full toggle history" scope as the
     * checkbox itself.
     *
     * @return list<array<string, mixed>>
     */
    private function buildTimeline(Order $order): array
    {
        $events = [];

        foreach ($order->stageChanges as $change) {
            $events[] = [
                'type' => 'stage_change',
                'from_stage' => $change->from_stage?->value,
                'to_stage' => $change->to_stage->value,
                'user_name' => $change->changedByUser?->name,
                'at' => $change->created_at?->toIso8601String(),
            ];
        }

        if ($order->quality_checked_at !== null) {
            $events[] = [
                'type' => 'quality_check',
                'user_name' => $order->qualityCheckedByUser?->name,
                'at' => $order->quality_checked_at->toIso8601String(),
            ];
        }

        foreach ($order->notes as $note) {
            $events[] = [
                'type' => 'note',
                'body' => $note->body,
                'user_name' => $note->user?->name,
                'at' => $note->created_at?->toIso8601String(),
            ];
        }

        usort($events, fn ($a, $b) => $b['at'] <=> $a['at']);

        return $events;
    }

    private function ensureBelongsToCurrentShop(Order $order): void
    {
        abort_unless($order->shop_id === Shop::current()->id, 404);
    }
}
