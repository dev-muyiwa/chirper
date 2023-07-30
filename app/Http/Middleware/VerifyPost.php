<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPost
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
            return (new Controller)->onFailure(null,"Post doesn't exist.",  404);
        }

        return $next($request);
    }
}
