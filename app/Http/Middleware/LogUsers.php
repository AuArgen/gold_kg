<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LogUser;
use Illuminate\Support\Facades\Auth;

class LogUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Запоминаем время начала выполнения запроса
        $startTime = microtime(true);

        // Продолжаем выполнение запроса и получаем ответ
        $response = $next($request);

        // Время окончания выполнения запроса
        $endTime = microtime(true);

        // Рассчитываем время выполнения в миллисекундах
        $responseTime = round(($endTime - $startTime) * 1000, 3);

        try {
            // Исключаем логирование для служебных запросов (например, для парсинга, если он вызывает много редиректов или AJAX)
            if ($request->route() && $request->route()->getName() === 'admin.gold.parse') {
                return $response;
            }

            // Логирование после получения ответа
            LogUser::create([
                'user_id' => Auth::check() ? Auth::id() : null,
                'ip_address' => $request->ip(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route_name' => $request->route() ? $request->route()->getName() : null,
                'status_code' => $response->getStatusCode(),
                'response_time' => $responseTime,
                // Логируем только непустые данные, исключая токены и большие файлы
                'request_data' => count($request->except(['_token', '_method'])) > 0 ? $request->except(['_token', '_method']) : null,
            ]);
        } catch (\Exception $e) {
            // В случае ошибки логирования (например, DB недоступна), просто пропускаем.
            // Можно добавить запись в обычный Laravel лог для дебага: \Log::error('LogUsers Middleware failed: ' . $e->getMessage());
        }

        return $response;
    }
}
