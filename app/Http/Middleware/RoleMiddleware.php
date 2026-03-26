<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $userRole = auth()->user()?->role?->value;

        if (! in_array($userRole, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
