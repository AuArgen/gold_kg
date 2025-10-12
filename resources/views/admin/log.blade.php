@extends('public.layout.base')

@section('title', 'Логирование активности пользователей')
@section('description', 'Просмотр всех запросов и действий пользователей в системе.')

@section('content')
    {{--
        Общий контейнер для центрирования макета
    --}}
    <div class="max-w-7xl mx-auto my-2 py-8 sm:py-12 min-h-screen">

        {{-- Основной Drawer --}}
        <div class="drawer lg:drawer-open min-h-full">

            {{-- 1. Скрытый чекбокс --}}
            <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

            {{-- 2. Основная контентная область (Контент) --}}
            <div class="drawer-content flex flex-col">

                {{-- Кнопка для открытия меню (мобильная) --}}
                <div class="lg:hidden px-4 mb-4">
                    <label for="my-drawer-2" class="btn bg-base-100 drawer-button shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        Меню
                    </label>
                </div>

                {{-- Контент Таблицы --}}
                <div class="flex-grow p-6 sm:p-10 bg-base-100 rounded-box shadow-xl mx-4 lg:ml-0 lg:mr-0">
                    <h1 class="text-3xl font-bold mb-8 text-center text-base-content">
                        Журнал запросов пользователей
                    </h1>

                    @if($logs->isEmpty())
                        <div role="alert" class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Журнал запросов пока пуст.</span>
                        </div>
                    @else
                        {{-- Контейнер для адаптивной таблицы (скролл по горизонтали на мобильных) --}}
                        <div class="overflow-x-auto rounded-box border border-base-200 shadow-lg">
                            <table class="table table-zebra w-full text-sm">

                                {{-- Заголовок --}}
                                <thead class="bg-base-200">
                                <tr class="text-base-content uppercase">
                                    <th>Дата</th>
                                    <th>Метод</th>
                                    <th>URL / Роут</th>
                                    <th class="text-center">Статус</th>
                                    <th class="text-center">Время (мс)</th>
                                    <th>IP-адрес</th>
                                    <th>User ID</th>
                                    <th>Данные</th>
                                </tr>
                                </thead>

                                {{-- Тело таблицы --}}
                                <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        {{-- Дата/Время --}}
                                        <td class="whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>

                                        {{-- Метод --}}
                                        <td>
                                            @php
                                                $methodColor = match($log->method) {
                                                    'GET' => 'badge-info',
                                                    'POST' => 'badge-success',
                                                    'PUT', 'PATCH' => 'badge-warning',
                                                    'DELETE' => 'badge-error',
                                                    default => 'badge-neutral',
                                                };
                                            @endphp
                                            <div class="badge {{ $methodColor }} font-bold text-xs">{{ $log->method }}</div>
                                        </td>

                                        {{-- URL / Роут (обрезается) --}}
                                        <td class="max-w-xs sm:max-w-sm truncate" title="{{ $log->url }}">
                                            <span class="font-semibold">{{ $log->route_name ?? 'N/A' }}</span>
                                            <span class="text-xs block text-base-content/70">{{ Str::limit($log->url, 40) }}</span>
                                        </td>

                                        {{-- Статус --}}
                                        <td class="text-center">
                                            @php
                                                $statusColor = match(true) {
                                                    $log->status_code >= 200 && $log->status_code < 300 => 'badge-success',
                                                    $log->status_code >= 300 && $log->status_code < 400 => 'badge-info',
                                                    $log->status_code >= 400 && $log->status_code < 500 => 'badge-warning',
                                                    default => 'badge-error',
                                                };
                                            @endphp
                                            <div class="badge {{ $statusColor }} text-xs">{{ $log->status_code }}</div>
                                        </td>

                                        {{-- Время ответа (мс) --}}
                                        <td class="text-center whitespace-nowrap">{{ $log->response_time }}</td>

                                        {{-- IP-адрес --}}
                                        <td class="text-xs">{{ $log->ip_address }}</td>

                                        {{-- User ID --}}
                                        <td class="text-center font-medium">
                                            {{ $log->user_id ?? 'Гость' }}
                                        </td>

                                        {{-- Данные запроса (Кнопка-модалка) --}}
                                        <td>
                                            {{-- ИСПРАВЛЕНО: Проверяем, является ли это массивом, и не используем json_decode() --}}
                                            @if(is_array($log->request_data) && count($log->request_data) > 0)
                                                {{-- Используем DaisyUI модальное окно --}}
                                                <label for="modal_{{ $log->id }}" class="btn btn-xs btn-outline btn-primary">
                                                    Показать
                                                </label>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                                {{-- Футер для пагинации --}}
                                <tfoot>
                                <tr>
                                    <td colspan="8" class="p-4 bg-base-200">
                                        {{ $logs->links('pagination::tailwind') }}
                                    </td>
                                </tr>
                                </tfoot>

                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. Sidebar (Левая часть) --}}
            @include('client.component.left_navbar')
        </div>
    </div>

    {{-- Модальные окна для просмотра данных запроса (вне основного контейнера для корректного z-index) --}}
    @foreach($logs as $log)
        {{-- ИСПРАВЛЕНО: Проверяем, является ли это массивом, и не используем json_decode() --}}
        @if(is_array($log->request_data) && count($log->request_data) > 0)
            <input type="checkbox" id="modal_{{ $log->id }}" class="modal-toggle" />
            <div class="modal" role="dialog">
                <div class="modal-box w-11/12 max-w-2xl">
                    <h3 class="font-bold text-lg">Данные запроса (ID: {{ $log->id }})</h3>
                    {{-- ИСПРАВЛЕНО: Просто кодируем массив обратно в JSON для отображения --}}
                    <pre class="whitespace-pre-wrap p-4 bg-base-200 rounded-lg mt-4 text-sm">{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    <div class="modal-action">
                        <label for="modal_{{ $log->id }}" class="btn btn-sm btn-outline">Закрыть</label>
                    </div>
                </div>
                <label class="modal-backdrop" for="modal_{{ $log->id }}">Закрыть</label>
            </div>
        @endif
    @endforeach

@endsection
