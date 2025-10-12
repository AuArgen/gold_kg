<?php

namespace App\Http\Controllers\public;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    /**
     * Обрабатывает отправку формы контактов, сохраняет в базу и применяет лимит.
     */
    public function submit(Request $request)
    {
        // 1. Валидация данных
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // 2. Идентификация пользователя для кэша и лимита
        // Ключ кэша зависит от того, авторизован пользователь или нет.
        // Если авторизован, используем ID, иначе - IP-адрес.
        $userIdentifier = Auth::check() ? 'auth:' . Auth::id() : 'ip:' . $request->ip();
        $cacheKey = 'contact_submission_' . $userIdentifier;
        $cooldownMinutes = 5;

        // 3. Проверка кэша/лимита
        if (Cache::has($cacheKey)) {
            // Пользователь отправил форму менее 5 минут назад.
            $remainingSeconds = Cache::get($cacheKey) - time();
            $minutes = ceil($remainingSeconds / 60);

            // Редирект с ошибкой
            return back()->withInput()->withErrors([
                'submission_limit' => "Вы можете отправить следующее сообщение через $minutes минут. Пожалуйста, подождите."
            ])->with('error', "Вы можете отправить следующее сообщение через $minutes минут.");
        }

        try {
            // 4. Сохранение записи в базу данных
            ContactSubmission::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'message' => $validated['message'],
                'status' => 'new', // По умолчанию 'new'
            ]);

            // 5. Установка кэша на 5 минут (300 секунд)
            // Кэш будет хранить метку времени, когда пользователь снова сможет отправить форму.
            Cache::put($cacheKey, time() + ($cooldownMinutes * 60), $cooldownMinutes * 60);

            // 6. Успешный редирект
            return back()->with('success', 'Ваше сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.');

        } catch (\Exception $e) {
            // Логирование и обработка ошибки базы данных
            \Log::error('Contact form submission failed: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Произошла ошибка при сохранении сообщения. Попробуйте позже.');
        }
    }
}
