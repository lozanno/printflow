<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShopRequest;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('admin/settings/shop', [
            'shop' => Shop::current(),
        ]);
    }

    public function update(UpdateShopRequest $request): RedirectResponse
    {
        $shop = Shop::current();

        $attributes = [
            'name' => $request->validated('name'),
            'currency' => $request->validated('currency'),
            'pickup_line1' => $request->validated('pickup_line1'),
            'pickup_line2' => $request->validated('pickup_line2'),
            'pickup_city' => $request->validated('pickup_city'),
            'pickup_state' => $request->validated('pickup_state'),
            'pickup_postal_code' => $request->validated('pickup_postal_code'),
            'pickup_phone' => $request->validated('pickup_phone'),
            'brand_color' => $request->validated('brand_color'),
            'contact_email' => $request->validated('contact_email'),
            'facebook_url' => $request->validated('facebook_url'),
            'instagram_url' => $request->validated('instagram_url'),
            'whatsapp_url' => $request->validated('whatsapp_url'),
        ];

        if ($request->hasFile('logo')) {
            if ($shop->logo_path) {
                Storage::disk('public')->delete($shop->logo_path);
            }

            $attributes['logo_path'] = $request->file('logo')->store('shop', 'public');
        }

        $shop->update($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Ajustes actualizados.')]);

        return to_route('admin.settings.edit');
    }
}
