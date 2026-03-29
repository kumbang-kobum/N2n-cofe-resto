<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $route = $request->route();
        $routeName = $route?->getName();

        if (! $user || ! $routeName) {
            return $next($request);
        }

        $patterns = collect(config('menu_permissions.route_patterns', []))
            ->sortByDesc(fn ($permission, $pattern) => strlen($pattern));

        foreach ($patterns as $pattern => $permission) {
            if (! Str::is($pattern, $routeName)) {
                continue;
            }

            abort_unless($user->can($permission), 403, 'Anda tidak memiliki hak akses ke menu ini.');
            break;
        }

        return $next($request);
    }
}
