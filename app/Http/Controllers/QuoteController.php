<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\Shop;
use App\QuoteEngine\Exceptions\QuoteCannotBeCalculatedException;
use App\QuoteEngine\PriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function store(Request $request, CatalogProduct $catalogProduct, PriceCalculator $calculator): JsonResponse
    {
        abort_unless(
            $catalogProduct->is_active && $catalogProduct->shop_id === Shop::current()->id,
            404,
        );

        $selections = $request->input('selections', []);

        abort_unless(is_array($selections), 422);

        try {
            $quote = $calculator->calculate($catalogProduct, $selections);
        } catch (QuoteCannotBeCalculatedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'base_price' => $quote->basePrice,
            'modifiers' => array_map(
                fn ($modifier) => ['label' => $modifier->label, 'amount' => $modifier->amount],
                $quote->modifiers,
            ),
            'total' => $quote->total,
            'currency' => $quote->currency,
        ]);
    }
}
