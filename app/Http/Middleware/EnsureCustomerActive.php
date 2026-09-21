<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A customer the team has blocked keeps a valid token until it expires.
 * Refuse every authenticated call for them and pull their tokens, so a
 * block in the panel takes effect on the next request, not next year.
 */
class EnsureCustomerActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof Customer && $user->status === 'blocked') {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'This account has been suspended. Please contact support.',
                'code' => 'account_blocked',
            ], 403);
        }

        return $next($request);
    }
}
