<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/users/index', [
            'users' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuario creado.')]);

        return to_route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/edit', [
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureNotOnlyAdmin($user, UserRole::from($request->validated('role')));

        $user->fill($request->safe()->except('password'));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuario actualizado.')]);

        return to_route('admin.users.edit', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, __('No puedes eliminar tu propia cuenta.'));

        $this->ensureNotOnlyAdmin($user, null);

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuario eliminado.')]);

        return to_route('admin.users.index');
    }

    /**
     * Blocks a change that would leave the shop with zero Admin users -
     * demoting or deleting the last one would lock everyone out with no
     * UI left to fix it.
     */
    private function ensureNotOnlyAdmin(User $user, ?UserRole $newRole): void
    {
        if ($user->role !== UserRole::Admin || $newRole === UserRole::Admin) {
            return;
        }

        $otherAdminsExist = User::where('role', UserRole::Admin)
            ->where('id', '!=', $user->id)
            ->exists();

        abort_unless($otherAdminsExist, 422, __('Debe existir al menos un administrador.'));
    }
}
