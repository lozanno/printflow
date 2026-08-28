<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $shop = Shop::current();

        return Inertia::render('admin/orders/index', [
            'orders' => $shop->orders()
                ->with(['customer', 'items.catalogProduct.productTemplate'])
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
                    'created_at' => $order->created_at?->toIso8601String(),
                ]),
        ]);
    }
}
