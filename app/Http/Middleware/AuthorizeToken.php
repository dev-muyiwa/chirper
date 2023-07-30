<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeToken
{
    /**
     * Restricts user from accessing unauthorised routes.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $handle = $request->route()->parameter("handle");
        $user = Auth::user();

        if (!$user){
            return (new Controller)->onFailure(null, "User not found.", 404);

        }

        if ($user->id !== $handle && $user->handle !== $handle) {
            return (new Controller)->onFailure(null, "You are not authorized to access this resource.", 403);
        }
        return $next($request);
    }
}
