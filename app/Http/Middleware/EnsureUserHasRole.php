<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a route behind having any role assigned at all (`role`, no args -
 * every role's job touches orders), or one of a specific set of roles
 * (`role:ADMIN`). A user with no role yet - freshly self-registered, not
 * yet set up by an admin - is blocked from every /admin route until
 * someone assigns them one; see the users.store role assignment flow.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if($user === null || $user->role === null, 403);

        abort_if($roles !== [] && ! in_array($user->role->value, $roles, true), 403);

        return $next($request);
    }
}
