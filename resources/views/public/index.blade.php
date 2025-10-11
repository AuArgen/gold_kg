@extends('public.layout.base')

@section('title', 'Главная страница')

@section('content')
    {{-- Скрипт для работы калькуляторов --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latestPrices = @json($latestPrices);

            // -----------------------------------------------------------------
            // 1. КАЛЬКУЛЯТОР "СКОЛЬКО СТОИТ?" (По количеству слитков)
            // -----------------------------------------------------------------
            const totalCostOutput = document.getElementById('total-cost-output');
            const quantityInputs = document.querySelectorAll('.quantity-input');

            function calculateTotalCost() {
                let totalKopecks = 0;
                let hasInput = false;

                quantityInputs.forEach(input => {
                    const goldId = parseInt(input.dataset.goldId);
                    const quantity = parseInt(input.value) || 0;

                    if (quantity > 0) {
                        hasInput = true;
                        const priceData = latestPrices.find(p => p.gold_id === goldId);

                        if (priceData) {
                            // Используем цену продажи (sale_kopecks)
                            totalKopecks += priceData.sale_kopecks * quantity;
                        }
                    }
                });

                if (hasInput) {
                    const totalSom = (totalKopecks / 100).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
                    totalCostOutput.textContent = `Общая стоимость покупки: ${totalSom} сом`;
                } else {
                    totalCostOutput.textContent = 'Введите количество слитков для расчета.';
                }
            }

            quantityInputs.forEach(input => {
                input.addEventListener('input', calculateTotalCost);
                // Запуск при загрузке, чтобы показать начальный текст
                calculateTotalCost();
            });


            // -----------------------------------------------------------------
            // 2. КАЛЬКУЛЯТОР "КУДА ВЛОЖИТЬ?" (По сумме)
            // -----------------------------------------------------------------
            const budgetInput = document.getElementById('budget-input');
            const investmentAdvice = document.getElementById('investment-advice');
            const goldItems = @json($golds->keyBy('id'));

            function getAdvice(budget) {
                if (budget <= 0) return [{ text: "Введите положительную сумму для получения совета.", type: 'info' }];

                const availablePrices = latestPrices
                    .filter(p => p.sale_kopecks <= budget * 100) // Фильтруем те, что можем купить
                    .map(p => ({
                        ...p,
                        weight: goldItems[p.gold_id].name, // Добавляем вес для отображения
                        priceSom: p.sale_kopecks / 100
                    }))
                    .sort((a, b) => b.priceSom - a.priceSom); // Сортируем от самого дорогого

                if (availablePrices.length === 0) {
                    return [{ text: "Вашего бюджета недостаточно для покупки самого маленького слитка.", type: 'warning' }];
                }

                const adviceList = [];
                const maxBudgetKopecks = budget * 100;

                // Совет 1: Максимальное количество самого маленького слитка
                const smallest = availablePrices[availablePrices.length - 1];
                if (smallest) {
                    const count = Math.floor(maxBudgetKopecks / smallest.sale_kopecks);
                    const totalCost = (count * smallest.sale_kopecks) / 100;
                    const remainder = (maxBudgetKopecks - (count * smallest.sale_kopecks)) / 100;
                    adviceList.push({
                        text: `**Вариант 1 (Ликвидность):** Купите **${count} шт. по ${smallest.weight}г** (Общая стоимость: ${totalCost.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ")} сом). Остаток: ${remainder.toFixed(2)} сом.`,
                        type: 'info'
                    });
                }

                // Совет 2: Один самый большой слиток, который доступен
                const largestSingle = availablePrices[0];
                if (largestSingle && largestSingle.id !== smallest.id) {
                    const remainder = (maxBudgetKopecks - largestSingle.sale_kopecks) / 100;
                    adviceList.push({
                        text: `**Вариант 2 (Экономия):** Купите **1 шт. по ${largestSingle.weight}г** (Общая стоимость: ${largestSingle.priceSom.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ")} сом). Остаток: ${remainder.toFixed(2)} сом.`,
                        type: 'success'
                    });
                }

                // Совет 3: Комбинированный (1 большой + остаток на маленькие)
                if (availablePrices.length > 1) {
                    const secondLargest = availablePrices.length > 1 ? availablePrices[1] : availablePrices[0];
                    if (secondLargest && secondLargest.id !== smallest.id) {
                        const remainingBudget = maxBudgetKopecks - secondLargest.sale_kopecks;
                        const countSmall = remainingBudget > 0 ? Math.floor(remainingBudget / smallest.sale_kopecks) : 0;

                        let totalCost = secondLargest.sale_kopecks;
                        let combinationText = `1 шт. по ${secondLargest.weight}г`;

                        if (countSmall > 0) {
                            totalCost += countSmall * smallest.sale_kopecks;
                            combinationText += ` и ${countSmall} шт. по ${smallest.weight}г`;
                        }

                        const remainder = (maxBudgetKopecks - totalCost) / 100;
                        totalCost = totalCost / 100;

                        adviceList.push({
                            text: `**Вариант 3 (Комбинированный):** Купите **${combinationText}** (Общая стоимость: ${totalCost.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ")} сом). Остаток: ${remainder.toFixed(2)} сом.`,
                            type: 'warning'
                        });
                    }
                }

                return adviceList.slice(0, 3); // Возвращаем максимум 3 совета
            }

            function updateAdvice() {
                const budget = parseFloat(budgetInput.value) || 0;
                const advice = getAdvice(budget);
                investmentAdvice.innerHTML = ''; // Очистка

                advice.forEach(item => {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${item.type} shadow-lg mb-3`;
                    alertDiv.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium">${item.text.replace(/\*\*/g, '<strong>').replace(/\*\*/g, '</strong>')}</span>
                    `;
                    investmentAdvice.appendChild(alertDiv);
                });
            }

            budgetInput.addEventListener('input', updateAdvice);
            updateAdvice(); // Первичный вызов
        });

        // Функция форматирования чисел (для таблицы)
        function formatNumber(kopecks) {
            return (kopecks / 100).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        }
    </script>

    <div class="max-w-7xl mx-auto my-4 px-4 sm:px-6 lg:px-8 min-h-screen">

        {{-- Приветствие и последняя дата обновления --}}
        <header class="text-center py-12 bg-base-100 rounded-xl shadow-2xl mb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-primary mb-4">
                Слитки золота {{ env('APP_NAME') }}
            </h1>
            <p class="text-lg text-base-content/80">
                Актуальные цены и удобные калькуляторы для инвестиций в драгоценные металлы.
            </p>
            @if($latestPublicDate)
                <div class="badge badge-lg badge-neutral mt-4 shadow-md">
                    Последнее обновление цен: {{ \Carbon\Carbon::parse($latestPublicDate)->format('d.m.Y') }}
                </div>
            @endif
        </header>

        {{-- Секция с последними ценами --}}
        <section class="mb-12">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Последние цены продажи</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                @forelse($latestPrices as $price)
                    <div class="card bg-base-200 shadow-xl border border-base-300 transform hover:scale-[1.03] transition duration-300">
                        <div class="card-body p-5 text-center">
                            <h3 class="text-xl font-bold text-base-content mb-1">{{ $price->gold->name ?? 'N/A' }} г</h3>
                            <p class="text-2xl font-extrabold text-primary">
                                {{ number_format($price->sale_kopecks / 100, 2, '.', ' ') }} <span class="text-sm font-normal">сом</span>
                            </p>
                            @php
                                $diff = $price->difference_sale_kopecks ?? 0;
                                $absDiff = abs($diff) / 100;
                                $colorClass = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-error' : 'text-base-content/60');
                                $icon = $diff > 0 ? '▲' : ($diff < 0 ? '▼' : '—');
                            @endphp
                            <span class="text-sm font-medium {{ $colorClass }} mt-1">
                                {{ $icon }} {{ number_format($absDiff, 2, '.', ' ') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="alert col-span-full alert-warning shadow-lg">Нет данных о ценах.</div>
                @endforelse
            </div>
        </section>

        {{-- Секция Калькуляторов --}}
        <section class="mb-12">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Ваши помощники</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- 1. Калькулятор "Куда вложить?" --}}
                <div class="card bg-base-100 shadow-2xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-bold text-secondary">Куда вложить? (По сумме)</h3>
                        <p class="text-base-content/70 mb-4">Введите сумму, которую вы готовы инвестировать, и получите три выгодных варианта покупки.</p>

                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text">Ваш бюджет (сом)</span>
                            </div>
                            <input
                                id="budget-input"
                                type="number"
                                placeholder="Например, 60000"
                                class="input input-bordered w-full bg-base-200"
                                min="1"
                            />
                        </label>

                        <div id="investment-advice" class="mt-4">
                            <div class="alert alert-info shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Введите сумму для получения рекомендаций.</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Калькулятор "Сколько стоит?" --}}
                <div class="card bg-base-100 shadow-2xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-bold text-secondary">Сколько стоит? (По количеству)</h3>
                        <p class="text-base-content/70 mb-4">Укажите количество слитков каждого веса для быстрого расчета общей суммы покупки.</p>

                        <div class="grid grid-cols-2 gap-4 max-h-60 overflow-y-auto pr-2">
                            @foreach($golds as $gold)
                                <label class="form-control">
                                    <div class="label">
                                        <span class="label-text">{{ $gold->name }} г</span>
                                    </div>
                                    <input
                                        type="number"
                                        data-gold-id="{{ $gold->id }}"
                                        placeholder="0 шт."
                                        class="input input-bordered w-full quantity-input bg-base-200"
                                        min="0"
                                    />
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 p-4 bg-base-200 rounded-lg shadow-inner">
                            <p id="total-cost-output" class="text-xl font-extrabold text-primary text-center">
                                Введите количество слитков для расчета.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- Секция Истории Цен с Пагинацией --}}
        <section class="mb-12 bg-base-100 p-6 rounded-xl shadow-2xl">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Архив цен на мерные слитки</h2>

            @if($allPrices->isEmpty())
                <div class="alert alert-info shadow-lg">Данные о ценах на золото не найдены.</div>
            @else
                {{-- Адаптивная таблица с прокруткой --}}
                <div class="overflow-x-auto rounded-box border border-base-300 shadow-md">
                    <table class="table table-zebra w-full text-base">
                        <thead class="bg-base-200">
                        <tr class="text-base-content">
                            <th>Дата</th>
                            <th class="text-center">Вес (гр)</th>
                            <th class="text-right">Покупка (сом)</th>
                            <th class="text-right">Продажа (сом)</th>
                            <th class="text-center md:table-cell hidden">Δ Покупка</th>
                            <th class="text-center">Δ Продажа</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($allPrices as $price)
                            <tr>
                                {{-- Дата --}}
                                <td class="font-semibold whitespace-nowrap">{{ \Carbon\Carbon::parse($price->public_date)->format('d.m.Y') }}</td>

                                {{-- Вес --}}
                                <td class="font-medium whitespace-nowrap text-center">
                                    {{ $price->gold->name ?? 'N/A' }} г
                                </td>

                                {{-- Цена Покупки --}}
                                <td class="text-right whitespace-nowrap">
                                    {{ number_format($price->buy_in_kopecks / 100, 2, '.', ' ') }}
                                </td>

                                {{-- Цена Продажи --}}
                                <td class="text-right whitespace-nowrap">
                                    {{ number_format($price->sale_kopecks / 100, 2, '.', ' ') }}
                                </td>

                                {{-- Разница Покупки (Скрыта на мобильных) --}}
                                <td class="text-center whitespace-nowrap md:table-cell hidden">
                                    @php
                                        $diff = $price->difference_buy_in_kopecks ?? 0;
                                        $absDiff = abs($diff) / 100;
                                        $colorClass = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-error' : 'text-base-content/60');
                                        $icon = $diff > 0 ? '▲' : ($diff < 0 ? '▼' : '—');
                                    @endphp
                                    <span class="{{ $colorClass }} flex items-center justify-center whitespace-nowrap">
                                            <span class="mr-1 text-lg leading-none">{{ $icon }}</span>
                                            {{ number_format($absDiff, 2, '.', ' ') }}
                                        </span>
                                </td>

                                {{-- Разница Продажи --}}
                                <td class="text-center whitespace-nowrap">
                                    @php
                                        $diff = $price->difference_sale_kopecks ?? 0;
                                        $absDiff = abs($diff) / 100;
                                        $colorClass = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-error' : 'text-base-content/60');
                                        $icon = $diff > 0 ? '▲' : ($diff < 0 ? '▼' : '—');
                                    @endphp
                                    <span class="{{ $colorClass }} flex items-center justify-center whitespace-nowrap">
                                            <span class="mr-1 text-lg leading-none">{{ $icon }}</span>
                                            {{ number_format($absDiff, 2, '.', ' ') }}
                                        </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                        {{-- Пагинация --}}
                        <tfoot>
                        <tr>
                            <td colspan="6" class="p-4 bg-base-200">
                                {{ $allPrices->links('pagination::tailwind') }}
                            </td>
                        </tr>
                        </tfoot>

                    </table>
                </div>
            @endif
        </section>

    </div>
@endsection
