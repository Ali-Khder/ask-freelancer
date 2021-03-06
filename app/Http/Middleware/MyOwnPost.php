<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Http\Traits;

class MyOwnPost
{
    use Traits\ResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $post = Post::find($request->id);
        $user = User::find(auth()->user()->id);

        if ($post->user_id == $user->id) {
            return $next($request);
        }
        return $this->failed('ليس لديك الصلاحية بالوصول الى هذا المنشور');
    }
}
