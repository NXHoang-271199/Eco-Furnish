<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        if (!Auth::user()->hasPermission($permissions)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
} 