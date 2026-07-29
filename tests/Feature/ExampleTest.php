<?php

use App\Models\Shop;

test('returns a successful response', function () {
    Shop::create(['name' => 'Test Shop', 'slug' => 'test-shop', 'currency' => 'MXN']);

    $response = $this->get(route('home'));

    $response->assertOk();
});