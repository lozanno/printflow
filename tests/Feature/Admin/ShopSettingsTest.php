<?php

use App\Models\User;

it('redirects guests', function () {
    makeShop();

    $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
    $this->put(route('admin.settings.update'))->assertRedirect(route('login'));
});

it('updates the shop branding and contact fields', function () {
    $shop = makeShop();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), [
            'name' => 'Nueva Imprenta',
            'currency' => 'USD',
            'brand_color' => '#ff0000',
            'accent_color' => '#00ff00',
            'contact_email' => 'hola@nuevaimprenta.mx',
            'facebook_url' => 'https://facebook.com/nuevaimprenta',
            'instagram_url' => 'https://instagram.com/nuevaimprenta',
            'whatsapp_url' => 'https://wa.me/5215500000000',
        ])
        ->assertRedirect(route('admin.settings.edit'));

    $shop->refresh();

    expect($shop->name)->toBe('Nueva Imprenta')
        ->and($shop->currency)->toBe('USD')
        ->and($shop->brand_color)->toBe('#ff0000')
        ->and($shop->accent_color)->toBe('#00ff00')
        ->and($shop->contact_email)->toBe('hola@nuevaimprenta.mx')
        ->and($shop->facebook_url)->toBe('https://facebook.com/nuevaimprenta');
});

it('rejects a brand_color that is not a hex value', function () {
    makeShop();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), [
            'name' => 'Mi Imprenta',
            'currency' => 'MXN',
            'brand_color' => 'not-a-color',
        ])
        ->assertSessionHasErrors('brand_color');
});
