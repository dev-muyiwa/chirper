<?php

namespace App\Http\Middleware;

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
        $user_id = $request->route()->parameter("id");
        $user = Auth::user();

        if ($user->id !== $user_id && $user->handle !== $user_id) {
           return redirect(route("forbidden"));
        }
        return $next($request);
    }
}
