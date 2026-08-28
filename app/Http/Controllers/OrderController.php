<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\CatalogProduct;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shop;
use App\QuoteEngine\Exceptions\QuoteCannotBeCalculatedException;
use App\QuoteEngine\PriceCalculator;
use App\QuoteEngine\QuoteResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CatalogProduct $catalogProduct, PriceCalculator $calculator): RedirectResponse
    {
        $shop = Shop::current();

        abort_unless($catalogProduct->is_active && $catalogProduct->shop_id === $shop->id, 404);

        $data = $request->validated();

        try {
            $quote = $calculator->calculate($catalogProduct, $data['selections']);
        } catch (QuoteCannotBeCalculatedException $exception) {
            throw ValidationException::withMessages(['selections' => $exception->getMessage()]);
        }

        $order = DB::transaction(fn () => $this->createOrder($shop, $catalogProduct, $data, $quote));

        return to_route('orders.confirmation', $order);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createOrder(Shop $shop, CatalogProduct $catalogProduct, array $data, QuoteResult $quote): Order
    {
        $customer = Customer::query()->updateOrCreate(
            ['shop_id' => $shop->id, 'email' => $data['customer']['email']],
            ['name' => $data['customer']['name'], 'phone' => $data['customer']['phone'] ?? null],
        );

        $quantity = $data['selections']['quantity'] ?? null;
        $quantity = is_numeric($quantity) ? (int) $quantity : null;

        $order = $customer->orders()->create([
            'shop_id' => $shop->id,
            'delivery_type' => $data['delivery_type'],
            'status' => OrderStatus::PendingPayment,
            'subtotal' => $quote->total,
            'shipping_cost' => 0,
            'total' => $quote->total,
            'currency' => $quote->currency,
        ]);

        $order->items()->create([
            'catalog_product_id' => $catalogProduct->id,
            'selected_options' => $data['selections'],
            'quantity' => $quantity,
            'unit_price' => $quantity ? round($quote->total / $quantity, 2) : $quote->total,
            'line_total' => $quote->total,
        ]);

        if ($data['delivery_type'] === DeliveryType::Ship->value) {
            $order->shippingAddress()->create([
                'recipient_name' => $data['shipping']['recipient_name'],
                'phone' => $data['shipping']['phone'],
                'line1' => $data['shipping']['line1'],
                'line2' => $data['shipping']['line2'] ?? null,
                'city' => $data['shipping']['city'],
                'state' => $data['shipping']['state'],
                'postal_code' => $data['shipping']['postal_code'],
                'country' => $data['shipping']['country'] ?? 'MX',
            ]);
        }

        // No payment gateway is wired up yet - the demo checkout marks the
        // payment as settled immediately rather than leaving the order
        // stuck in PENDING_PAYMENT with nothing to reconcile it later.
        $order->payments()->create([
            'provider' => 'manual',
            'provider_reference' => (string) Str::uuid(),
            'method' => $data['payment_method'],
            'status' => PaymentStatus::Succeeded,
            'amount' => $order->total,
            'paid_at' => now(),
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        return $order;
    }

    public function confirmation(Order $order): Response
    {
        abort_unless($order->shop_id === Shop::current()->id, 404);

        $order->load('customer', 'items.catalogProduct.productTemplate');

        return Inertia::render('checkout/confirmation', [
            'order' => [
                'id' => $order->id,
                'status' => $order->status->value,
                'total' => (float) $order->total,
                'currency' => $order->currency,
                'customer_name' => $order->customer->name,
                'delivery_type' => $order->delivery_type->value,
                'product_names' => $order->items
                    ->map(fn ($item) => $item->catalogProduct->name_override ?? $item->catalogProduct->productTemplate->name)
                    ->all(),
            ],
        ]);
    }
}
