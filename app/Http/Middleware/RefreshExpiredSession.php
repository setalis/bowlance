<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RefreshExpiredSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, существует ли сессия и не истекла ли она
        if ($request->hasSession()) {
            $session = $request->session();
            $sessionId = $session->getId();

            // Если используется драйвер базы данных, проверяем время последней активности
            if (config('session.driver') === 'database' && $sessionId) {
                try {
                    $sessionLifetime = config('session.lifetime', 1440);
                    $lastActivity = DB::table(config('session.table', 'sessions'))
                        ->where('id', $sessionId)
                        ->value('last_activity');

                    // Если сессия не найдена в базе данных или истекла, создаем новую
                    if (! $lastActivity || (time() - $lastActivity) > ($sessionLifetime * 60)) {
                        $session->regenerate();
                    }
                } catch (\Exception $e) {
                    // Если произошла ошибка при проверке, создаем новую сессию
                    $session->regenerate();
                }
            } elseif ($sessionId && ! $session->has('_token')) {
                // Для других драйверов проверяем наличие токена
                // Если токен отсутствует, создаем новую сессию
                $session->regenerate();
            }
        }

        return $next($request);
    }
}
