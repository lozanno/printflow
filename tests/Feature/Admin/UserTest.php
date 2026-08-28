<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('redirects guests to login for every user route', function () {
    $user = User::factory()->create();

    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    $this->get(route('admin.users.create'))->assertRedirect(route('login'));
    $this->get(route('admin.users.edit', $user))->assertRedirect(route('login'));
    $this->post(route('admin.users.store'))->assertRedirect(route('login'));
    $this->put(route('admin.users.update', $user))->assertRedirect(route('login'));
    $this->delete(route('admin.users.destroy', $user))->assertRedirect(route('login'));
});

it('lists users with their role', function () {
    User::factory()->create(['name' => 'Ana Ventas', 'role' => UserRole::Ventas]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users', 2)
        );
});

it('creates a user with a role', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.store'), [
            'name' => 'Ana Ventas',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'VENTAS',
        ])
        ->assertRedirect(route('admin.users.index'));

    $created = User::where('email', 'ana@example.com')->sole();
    expect($created->role)->toBe(UserRole::Ventas);
});

it('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'ana@example.com']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.store'), [
            'name' => 'Otra Ana',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'VENTAS',
        ])
        ->assertSessionHasErrors('email');
});

it('updates a user without changing the password when left blank', function () {
    $target = User::factory()->role(UserRole::Ventas)->create();
    $originalPassword = $target->password;

    $this->actingAs(User::factory()->create())
        ->put(route('admin.users.update', $target), [
            'name' => 'Nombre Nuevo',
            'email' => $target->email,
            'role' => 'ADMINISTRATIVO',
        ])
        ->assertRedirect(route('admin.users.edit', $target));

    $target->refresh();
    expect($target->name)->toBe('Nombre Nuevo');
    expect($target->role)->toBe(UserRole::Administrativo);
    expect($target->password)->toBe($originalPassword);
});

it('updates the password when one is provided', function () {
    $target = User::factory()->role(UserRole::Ventas)->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => $target->role->value,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect(route('admin.users.edit', $target));

    expect(Hash::check('new-password-123', $target->fresh()->password))->toBeTrue();
});

it('deletes a user', function () {
    $target = User::factory()->role(UserRole::Ventas)->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.users.destroy', $target))
        ->assertRedirect(route('admin.users.index'));

    expect(User::find($target->id))->toBeNull();
});

it('refuses to delete your own account', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertForbidden();

    expect(User::find($admin->id))->not->toBeNull();
});

it('refuses to demote the only admin', function () {
    $onlyAdmin = User::factory()->create();

    $this->actingAs($onlyAdmin)
        ->put(route('admin.users.update', $onlyAdmin), [
            'name' => $onlyAdmin->name,
            'email' => $onlyAdmin->email,
            'role' => 'VENTAS',
        ])
        ->assertStatus(422);

    expect($onlyAdmin->fresh()->role)->toBe(UserRole::Admin);
});
