<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\Users;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperuser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Users::isSuperuser(Users::getLoggedUserId()))
        {
            $message = lpApiResponse(true, "You don't have permission to access this route.");
            return response()->json($message, Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
