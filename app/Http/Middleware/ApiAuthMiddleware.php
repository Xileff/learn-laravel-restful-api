<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        $authenticated = true;

        // Check if token exist
        if (!$token) {
            $authenticated = false;
        }

        // Check if token is real
        $user = User::where('token', $token)->first();
        if (!$user) {
            $authenticated = false;
        }

        // If token is real -> attach data user ke request, mirip kyk di req.user di Express
        if (!$authenticated) {
            return response()->json([
                'errors' => [
                    'message' => [
                        'unauthorized'
                    ]
                ]
            ])->setStatusCode(401);
        }

        Auth::login($user);
        return $next($request);
    }
}
