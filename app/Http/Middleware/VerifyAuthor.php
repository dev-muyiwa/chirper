<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyAuthor
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $post_id = $request->route()->parameter("postId");
        $post = Post::find($post_id);

        if (!$post) {
            return (new Controller)->onFailure("Resource doesn't exist.",  404);
        }

        if ($post->user()->isNot(Auth::user())) {
            return (new Controller)->onFailure("You are not authorised to make changes to this resource.", 403);
        }

        return $next($request);
    }
}
