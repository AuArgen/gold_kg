@extends('public.layout.base')

@section('title', 'Золото личный кабинет')

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
                        Цены на мерные слитки золота
                    </h1>

                    {{-- БЛОК С ФОРМОЙ ПАРСИНГА --}}
                    <div class="mb-8 p-4 bg-base-200 rounded-lg shadow-inner">
                        <h2 class="text-xl font-semibold mb-3 text-base-content">Парсинг цен</h2>

                        {{-- Блок для отображения статуса после парсинга --}}
                        @if (session('status'))
                            @php $status = session('status'); @endphp
                            <div class="alert alert-{{ $status['type'] }} mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    @if ($status['type'] == 'success')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @elseif ($status['type'] == 'error' || $status['type'] == 'warning')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.394 16c-.77 1.333.192 3 1.732 3z" />
                                    @endif
                                </svg>
                                <span>{{ $status['message'] }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.parse') }}" class="flex flex-col sm:flex-row gap-3">
                            @csrf
                            <label class="input input-bordered flex items-center gap-2 flex-grow bg-base-100">
                                URL сайта:
                                <input
                                    type="url"
                                    name="url"
                                    placeholder="https://example.com/gold-prices"
                                    class="grow border-none focus:outline-none bg-base-100"
                                    value="{{ old('url', 'https://www.nbkr.kg/printver.jsp?item=2746&lang=KGZ') }}"
                                    required
                                />
                            </label>
                            <button type="submit" class="btn btn-primary shadow-md w-full sm:w-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                                Парсинг цен золота
                            </button>
                        </form>
                        @error('url')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- КОНЕЦ БЛОКА С ФОРМОЙ ПАРСИНГА --}}

                    @if($prices->isEmpty())
                        <div class="alert alert-info shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Данные о ценах на золото не найдены.</span>
                        </div>
                    @else
                        {{-- Контейнер для адаптивной таблицы (скролл по горизонтали на мобильных) --}}
                        <div class="overflow-x-auto rounded-box border border-base-200 shadow-md">
                            <table class="table table-zebra w-full text-base">
                                {{-- Заголовок --}}
                                <thead class="bg-base-200">
                                <tr class="text-base-content">
                                    <th>Дата</th>
                                    <th class="text-center">Вес (гр)</th>
                                    <th class="text-right">Покупка (сом)</th>
                                    <th class="text-right">Продажа (сом)</th>
                                    <th class="text-center">Δ Покупка</th>
                                    <th class="text-center">Δ Продажа</th>
                                </tr>
                                </thead>

                                {{-- Тело таблицы --}}
                                <tbody>
                                @foreach($prices as $price)
                                    <tr>
                                        {{-- Дата --}}
                                        <td class="font-semibold whitespace-nowrap">{{ \Carbon\Carbon::parse($price->public_date)->format('d.m.Y') }}</td>

                                        {{-- Вес (Название) --}}
                                        <td class="font-medium whitespace-nowrap text-center">
                                            {{ $price->gold->name ?? 'N/A' }}
                                        </td>

                                        {{-- Цена Покупки (конвертируем из копеек) --}}
                                        <td class="text-right whitespace-nowrap">
                                            {{ number_format($price->buy_in_kopecks / 100, 2, '.', ' ') }}
                                        </td>

                                        {{-- Цена Продажи (конвертируем из копеек) --}}
                                        <td class="text-right whitespace-nowrap">
                                            {{ number_format($price->sale_kopecks / 100, 2, '.', ' ') }}
                                        </td>

                                        {{-- Разница Покупки (Встроенная логика бейджа) --}}
                                        <td class="text-center whitespace-nowrap">
                                            @php
                                                $diff = $price->difference_buy_in_kopecks ?? 0;
                                                $absDiff = abs($diff) / 100; // Конвертируем в сомы

                                                $colorClass = 'text-base-content/60';
                                                $icon = '—';

                                                if ($diff > 0) {
                                                    $colorClass = 'text-success font-bold';
                                                    $icon = '▲';
                                                } elseif ($diff < 0) {
                                                    $colorClass = 'text-error font-bold';
                                                    $icon = '▼';
                                                }
                                            @endphp
                                            <span class="{{ $colorClass }} flex items-center justify-center whitespace-nowrap">
                                                    <span class="mr-1 text-lg leading-none">{{ $icon }}</span>
                                                    {{ number_format($absDiff, 2, '.', ' ') }}
                                                </span>
                                        </td>

                                        {{-- Разница Продажи (Встроенная логика бейджа) --}}
                                        <td class="text-center whitespace-nowrap">
                                            @php
                                                $diff = $price->difference_sale_kopecks ?? 0;
                                                $absDiff = abs($diff) / 100; // Конвертируем в сомы

                                                $colorClass = 'text-base-content/60';
                                                $icon = '—';

                                                if ($diff > 0) {
                                                    $colorClass = 'text-success font-bold';
                                                    $icon = '▲';
                                                } elseif ($diff < 0) {
                                                    $colorClass = 'text-error font-bold';
                                                    $icon = '▼';
                                                }
                                            @endphp
                                            <span class="{{ $colorClass }} flex items-center justify-center whitespace-nowrap">
                                                    <span class="mr-1 text-lg leading-none">{{ $icon }}</span>
                                                    {{ number_format($absDiff, 2, '.', ' ') }}
                                                </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                                {{-- Футер для пагинации --}}
                                <tfoot class="bg-base-200">
                                <tr>
                                    <td colspan="6" class="p-4">
                                        {{ $prices->links('pagination::tailwind') }}
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
@endsection
