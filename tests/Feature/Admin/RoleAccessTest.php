<?php

use App\Enums\UserRole;
use App\Models\User;

it('blocks a user with no role from every admin route', function () {
    makeShop();
    $user = User::factory()->withoutRole()->create();

    $this->actingAs($user)->get(route('admin.orders.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
});

it('lets any assigned role view orders', function () {
    makeShop();

    foreach ([UserRole::Ventas, UserRole::Administrativo, UserRole::Produccion, UserRole::Calidad] as $role) {
        $user = User::factory()->role($role)->create();

        $this->actingAs($user)->get(route('admin.orders.index'))->assertOk();
    }
});

it('blocks non-admin roles from product/catalog configuration and staff management', function () {
    makeShop();

    foreach ([UserRole::Ventas, UserRole::Administrativo, UserRole::Produccion, UserRole::Calidad] as $role) {
        $user = User::factory()->role($role)->create();

        $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }
});

it('lets an admin access everything', function () {
    makeShop();
    $admin = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk();
    $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
});
