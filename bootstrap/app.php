<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // 1. Добавление псевдонима (alias) для использования в маршрутах (Route::middleware(['admin']))
        $middleware->alias([
            'admin' => App\Http\Middleware\RoleAdmin::class,
            'log' => App\Http\Middleware\LogUsers::class,
        ]);

        // 2. Если вы хотите добавить его в глобальный стек (для ВСЕХ запросов),
        // используйте: $middleware->appendToGroup('web', App\Http\Middleware\RoleAdmin::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
