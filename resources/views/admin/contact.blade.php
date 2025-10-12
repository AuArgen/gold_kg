@extends('public.layout.base')

@section('title', 'Сообщения от клиентов | ' . env('APP_NAME'))

@section('content')
    {{--
        Общий контейнер для центрирования макета
    --}}
    <div class="max-w-7xl mx-auto my-2 py-8 sm:py-12 min-h-screen">

        {{-- Основной Drawer --}}
        <div class="drawer lg:drawer-open min-h-full">

            {{-- 1. Скрытый чекбокс --}}
            <input id="my-drawer-2" type="checkbox" class="drawer-toggle"/>

            {{-- 2. Основная контентная область (Контент) --}}
            <div class="drawer-content flex flex-col">

                {{-- Кнопка для открытия меню (мобильная) --}}
                <div class="lg:hidden px-4 mb-4">
                    <label for="my-drawer-2" class="btn bg-base-100 drawer-button shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             class="inline-block w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        Меню
                    </label>
                </div>

                {{-- Контент Таблицы --}}
                <div class="flex-grow p-6 sm:p-10 bg-base-100 rounded-box shadow-xl mx-4 lg:ml-0 lg:mr-0">
                    <h1 class="text-3xl font-bold mb-8 text-center text-base-content">
                        Сообщения с формы "Контакты"
                    </h1>
                    <p class="text-center text-base-content/80 mt-2">
                        Все обращения, отправленные клиентами. Всего: {{ $submissions->total() }}
                    </p>


                    <div class="mb-4">
                        {{ $submissions->links('pagination::tailwind') }}
                    </div>

                    {{-- Таблица с записями --}}
                    <div class="overflow-x-auto bg-base-100 rounded-xl shadow-2xl border border-base-300">
                        <table class="table table-zebra w-full">
                            {{-- Заголовок таблицы --}}
                            <thead>
                            <tr class="text-base-content/70">
                                <th>Дата</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Статус</th>
                                <th class="w-1/3">Сообщение</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($submissions as $submission)
                                <tr class="hover:bg-base-200 transition duration-150">
                                    {{-- Дата создания --}}
                                    <td>
                                        <div
                                            class="font-bold">{{ \Carbon\Carbon::parse($submission->created_at)->format('d.m.Y') }}</div>
                                        <div
                                            class="text-sm opacity-50">{{ \Carbon\Carbon::parse($submission->created_at)->format('H:i') }}</div>
                                    </td>

                                    {{-- Имя --}}
                                    <td>
                                        <div class="font-semibold text-base-content">{{ $submission->name }}</div>
                                    </td>

                                    {{-- Email --}}
                                    <td>
                                        <a href="mailto:{{ $submission->email }}"
                                           class="link link-primary text-sm">{{ $submission->email }}</a>
                                    </td>

                                    {{-- Статус --}}
                                    <td>
                                        @php
                                            $badgeClass = match($submission->status) {
                                                'new' => 'badge-error',
                                                'in_progress' => 'badge-warning',
                                                'closed' => 'badge-success',
                                                default => 'badge-neutral',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} badge-outline font-semibold">
                                    {{ $submission->status }}
                                </span>
                                    </td>

                                    {{-- Сообщение (обрезанное) --}}
                                    <td>
                                        {{ Str::limit($submission->message, 100) }}
                                        {{-- Здесь можно добавить кнопку для просмотра полного сообщения в модальном окне --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-lg text-base-content/70">
                                        Сообщений пока нет.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Пагинация снизу --}}
                    <div class="mt-8">
                        {{ $submissions->links('pagination::tailwind') }}
                    </div>

                </div>
            </div>

            {{-- 3. Sidebar (Левая часть) --}}
            @include('client.component.left_navbar')
        </div>
    </div>
@endsection
