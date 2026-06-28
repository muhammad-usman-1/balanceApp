<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class BranchScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isBranchUser()) {
            // Block branch admins from accessing admin-only routes
            $blockedPrefixes = [
                'admin/categories',
                'admin/subcrption-plans',
                'admin/protein-options',
                'admin/branches',
                'admin/areas',
                'admin/coupons',
                'admin/notifications',
                'admin/affiliated-codes',
                'admin/users',
                'admin/roles',
                'admin/permissions',
                'admin/settings',
                'admin/meal-restrictions',
            ];

            foreach ($blockedPrefixes as $prefix) {
                if ($request->is($prefix) || $request->is($prefix . '/*')) {
                    abort(403, 'Access restricted to your branch only.');
                }
            }

            // Share branch context with all views
            View::share('branchId', $user->branch_id);
            View::share('branchName', $user->branch->name ?? '');
            View::share('isBranchUser', true);
        } else {
            View::share('branchId', null);
            View::share('branchName', '');
            View::share('isBranchUser', false);
        }

        return $next($request);
    }
}
