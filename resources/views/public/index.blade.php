@extends('public.layout.base')

@section('title', 'Актуальные цены на золотые слитки | Калькулятор инвестиций')
@section('description', 'Самые свежие цены на мерные золотые слитки в Кыргызстане. Динамика цен, калькуляторы прибыли и советы по инвестированию.')

@section('content')
    {{-- CDN для Chart.js (для построения графика) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latestPrices = @json($latestPrices);
            const goldItems = @json($golds->keyBy('id'));
            // !!! ВАЖНО: allHistoricalPrices передается как объект JSON. !!!
            const allHistoricalPrices = @json($allHistoricalPrices);

            // Получаем последнюю актуальную дату для сравнения в калькуляторе прибыли
            const latestDate = '{{ \Carbon\Carbon::parse($latestPublicDate)->format('Y-m-d') }}';
            const latestPricesMap = new Map();
            latestPrices.forEach(p => latestPricesMap.set(p.gold_id, p));

            // Функция форматирования чисел (с пробелом в качестве разделителя тысяч)
            function formatSom(kopecks) {
                if (kopecks === undefined || kopecks === null) return '0.00';
                return (kopecks / 100).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
            }

            // =================================================================
            // 1. КАЛЬКУЛЯТОР "СКОЛЬКО СТОИТ?" (По количеству слитков)
            // =================================================================
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
                        const priceData = latestPricesMap.get(goldId);

                        if (priceData) {
                            // Используем цену продажи (sale_kopecks)
                            totalKopecks += priceData.sale_kopecks * quantity;
                        }
                    }
                });

                if (hasInput) {
                    totalCostOutput.textContent = `Общая стоимость покупки: ${formatSom(totalKopecks)} сом`;
                } else {
                    totalCostOutput.textContent = 'Введите количество слитков для расчета.';
                }
            }

            quantityInputs.forEach(input => {
                input.addEventListener('input', calculateTotalCost);
                calculateTotalCost();
            });


            // =================================================================
            // 2. КАЛЬКУЛЯТОР "КУДА ВЛОЖИТЬ?" (По сумме)
            // =================================================================
            const budgetInput = document.getElementById('budget-input');
            const investmentAdvice = document.getElementById('investment-advice');

            function getAdvice(budget) {
                if (budget <= 0) return [{ text: "Введите положительную сумму для получения совета.", type: 'info' }];

                const availablePrices = latestPrices
                    .filter(p => p.sale_kopecks <= budget * 100)
                    .map(p => ({
                        ...p,
                        weight: goldItems[p.gold_id].name,
                        priceSom: p.sale_kopecks / 100
                    }))
                    .sort((a, b) => b.priceSom - a.priceSom);

                if (availablePrices.length === 0) {
                    return [{ text: "Вашего бюджета недостаточно для покупки самого маленького слитка.", type: 'warning' }];
                }

                const adviceList = [];
                const maxBudgetKopecks = budget * 100;

                // Совет 1: Максимальное количество самого маленького слитка (Ликвидность)
                const smallest = availablePrices[availablePrices.length - 1];
                if (smallest) {
                    const count = Math.floor(maxBudgetKopecks / smallest.sale_kopecks);
                    const totalCostK = count * smallest.sale_kopecks;
                    const remainderK = maxBudgetKopecks - totalCostK;
                    adviceList.push({
                        text: `**Вариант 1 (Ликвидность):** Купите **${count} шт. по ${smallest.weight}г** (Стоимость: ${formatSom(totalCostK)} сом). Остаток: ${formatSom(remainderK)} сом.`,
                        type: 'info'
                    });
                }

                // Совет 2: Один самый большой слиток, который доступен (Экономия)
                const largestSingle = availablePrices[0];
                if (largestSingle && largestSingle.id !== smallest.id) {
                    const remainderK = maxBudgetKopecks - largestSingle.sale_kopecks;
                    adviceList.push({
                        text: `**Вариант 2 (Экономия):** Купите **1 шт. по ${largestSingle.weight}г** (Стоимость: ${formatSom(largestSingle.sale_kopecks)} сом). Остаток: ${formatSom(remainderK)} сом.`,
                        type: 'success'
                    });
                }

                // Совет 3: Комбинированный (1 средний + остаток на маленькие)
                if (availablePrices.length > 1) {
                    const mediumSized = availablePrices.find(p => p.gold_id !== smallest.gold_id && p.gold_id !== largestSingle.gold_id) || largestSingle;
                    if (mediumSized) {
                        const remainingBudgetK = maxBudgetKopecks - mediumSized.sale_kopecks;
                        const countSmall = remainingBudgetK > 0 ? Math.floor(remainingBudgetK / smallest.sale_kopecks) : 0;

                        let totalCostK = mediumSized.sale_kopecks;
                        let combinationText = `1 шт. по ${mediumSized.weight}г`;

                        if (countSmall > 0) {
                            totalCostK += countSmall * smallest.sale_kopecks;
                            combinationText += ` и ${countSmall} шт. по ${smallest.weight}г`;
                        }

                        const remainderK = maxBudgetKopecks - totalCostK;

                        adviceList.push({
                            text: `**Вариант 3 (Комбинированный):** Купите **${combinationText}** (Стоимость: ${formatSom(totalCostK)} сом). Остаток: ${formatSom(remainderK)} сом.`,
                            type: 'warning'
                        });
                    }
                }

                return adviceList.slice(0, 3);
            }

            function updateAdvice() {
                const budget = parseFloat(budgetInput.value) || 0;
                const advice = getAdvice(budget);
                investmentAdvice.innerHTML = '';

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


            // =================================================================
            // 3. КАЛЬКУЛЯТОР ПРИБЫЛИ (По граммам) - ЛОГИКА ПЕРЕМЕЩЕНА В JS
            // =================================================================

            // Текущая цена покупки 1г (для расчета прибыли)
            const currentGramPriceK = latestPricesMap.get(1)?.buy_in_kopecks;

            // Элементы формы
            const customGramInput = document.getElementById('custom-gram-input');
            const purchaseDateSelect = document.getElementById('purchase-date-select');
            const purchaseGoldSelect = document.getElementById('purchase-gold-select');
            const calculateProfitButton = document.getElementById('calculate-profit-button');
            const profitOutput = document.getElementById('profit-output');
            const historicalPriceDisplay = document.getElementById('historical-price-display'); // Для отображения исторической цены


            // -----------------------------------------------------------------
            // 3.1. Функция поиска и отображения исторической цены (UI)
            // -----------------------------------------------------------------
            function updateHistoricalPriceUI() {
                const selectedDate = purchaseDateSelect.value;
                const selectedGoldId = parseInt(purchaseGoldSelect.value);

                let historicalPriceK = null;
                let ingotWeightDisplay = '';

                if (allHistoricalPrices[selectedDate]) {
                    const items = allHistoricalPrices[selectedDate];
                    // Find the target item (assuming items is an array of objects)
                    const targetItem = items.find(p => p.gold_id === selectedGoldId);

                    if (targetItem) {
                        // Цена, по которой банк продавал (Sale Price) - это цена покупки для пользователя
                        historicalPriceK = targetItem.sale_kopecks;
                        // Извлекаем вес из имени слитка (e.g., "1 г" -> "1")
                        ingotWeightDisplay = goldItems[selectedGoldId]?.name.replace(/[^0-9.]/g, '') || '1';
                    }
                }

                if (historicalPriceK !== null && historicalPriceK > 0) {
                    historicalPriceDisplay.innerHTML = `Историческая цена (продажи): <span class="font-bold text-primary">${formatSom(historicalPriceK)} сом за ${ingotWeightDisplay} г</span>`;
                    historicalPriceDisplay.classList.remove('text-error', 'text-warning');
                    historicalPriceDisplay.classList.add('text-base-content/80');
                    calculateProfitButton.disabled = false;
                } else {
                    historicalPriceDisplay.innerHTML = `
                        <span class="font-bold text-error">
                            Цены на ${selectedDate ? new Date(selectedDate).toLocaleDateString('ru-RU', {day: '2-digit', month: '2-digit', year: 'numeric'}) : 'выбранную дату'}
                            для ${goldItems[selectedGoldId]?.name || 'N/A'}г отсутствуют.
                        </span>
                    `;
                    historicalPriceDisplay.classList.remove('text-base-content/80', 'text-warning');
                    historicalPriceDisplay.classList.add('text-error');
                    // Если цены нет, кнопку расчета отключаем
                    calculateProfitButton.disabled = true;
                    profitOutput.innerHTML = '<span class="text-base-content/70">Выберите дату, для которой есть данные.</span>';
                }
            }


            // -----------------------------------------------------------------
            // 3.2. Функция расчета прибыли (Обработка клика по кнопке)
            // -----------------------------------------------------------------
            function calculateProfit() {
                // Очищаем вывод
                profitOutput.innerHTML = '';

                const grams = parseFloat(customGramInput.value) || 0;
                const selectedDate = purchaseDateSelect.value;
                const selectedGoldId = parseInt(purchaseGoldSelect.value);

                if (grams <= 0) {
                    profitOutput.innerHTML = '<div class="alert alert-warning shadow-lg text-sm">Пожалуйста, введите количество золота в граммах.</div>';
                    return;
                }

                let historicalPriceK = 0;

                if (allHistoricalPrices[selectedDate]) {
                    const items = allHistoricalPrices[selectedDate];
                    const targetItem = items.find(p => p.gold_id === selectedGoldId);

                    if (targetItem) {
                        historicalPriceK = targetItem.sale_kopecks;
                    }
                }

                if (historicalPriceK <= 0 || !currentGramPriceK) {
                    // Это сообщение должно быть заблокировано функцией updateHistoricalPriceUI
                    profitOutput.innerHTML = '<div class="alert alert-error shadow-lg text-sm">Ошибка: Историческая цена или текущая цена 1г отсутствует.</div>';
                    return;
                }

                // Получаем вес выбранного слитка в граммах (число)
                const ingotWeight = parseFloat(goldItems[selectedGoldId]?.name.replace(/[^0-9.]/g, '')) || 1;

                // 1. Считаем фактическую цену покупки
                // Цена за выбранный слиток / Вес слитка * общее количество грамм
                const totalCostK = (historicalPriceK / ingotWeight) * grams;

                // 2. Считаем текущую стоимость продажи
                // Текущая цена покупки 1г * количество грамм
                const currentValueK = currentGramPriceK * grams;

                const profitK = currentValueK - totalCostK;

                const profitSom = formatSom(profitK);
                const costSom = formatSom(totalCostK);
                const type = profitK >= 0 ? 'text-success' : 'text-error';
                const icon = profitK >= 0 ? '▲' : '▼';

                profitOutput.innerHTML = `
                    <div class="flex flex-col space-y-2 p-4 bg-base-300 rounded-lg shadow-md">
                        <p class="text-sm text-base-content font-medium">Ваша стоимость покупки: <span class="font-extrabold text-secondary">${costSom} сом</span></p>
                        <p class="text-sm text-base-content font-medium">Текущая стоимость продажи: <span class="font-extrabold text-primary">${formatSom(currentValueK)} сом</span></p>
                        <p class="text-lg ${type} font-bold border-t border-base-content/30 pt-2 mt-2">
                            Ваш потенциальный ${profitK >= 0 ? 'ДОХОД' : 'УБЫТОК'}: <span class="text-2xl">${icon} ${profitSom} сом</span>
                        </p>
                    </div>
                `;
            }

            // -----------------------------------------------------------------
            // 3.3. Привязка событий
            // -----------------------------------------------------------------
            purchaseDateSelect.addEventListener('change', updateHistoricalPriceUI);
            purchaseGoldSelect.addEventListener('change', updateHistoricalPriceUI);
            customGramInput.addEventListener('input', updateHistoricalPriceUI); // Для активации кнопки

            calculateProfitButton.addEventListener('click', calculateProfit);

            // Инициализация
            updateHistoricalPriceUI();


            // =================================================================
            // 4. ДИНАМИКА ЦЕН (ГРАФИК)
            // =================================================================
            const chartCanvas = document.getElementById('priceChart');
            const goldSelector = document.getElementById('gold-selector');
            let priceChartInstance;

            function generateChartData(selectedGoldId) {
                const dates = [];
                const prices = [];

                // Преобразуем объект в массив и сортируем по дате (старые -> новые)
                const sortedDates = Object.keys(allHistoricalPrices).sort();

                sortedDates.forEach(dateStr => {
                    const items = allHistoricalPrices[dateStr];
                    const targetItem = items.find(p => p.gold_id === parseInt(selectedGoldId));

                    if (targetItem) {
                        // Используем JS для форматирования даты в графике
                        dates.push(new Date(dateStr).toLocaleDateString('ru-RU', {day: '2-digit', month: '2-digit'}));
                        prices.push(targetItem.sale_kopecks / 100);
                    }
                });

                return { dates, prices };
            }

            function updateChart() {
                const selectedGoldId = goldSelector.value;
                const { dates, prices } = generateChartData(selectedGoldId);

                if (priceChartInstance) {
                    priceChartInstance.destroy();
                }

                priceChartInstance = new Chart(chartCanvas, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: `Цена продажи ${goldItems[selectedGoldId].name}г (сом)`,
                            data: prices,
                            borderColor: '#3b82f6', // blue-500 (Primary color)
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                title: {
                                    display: true,
                                    text: 'Цена (сом)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'KGS' }).format(context.raw);
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            goldSelector.addEventListener('change', updateChart);

            // Инициализация графика при загрузке
            if (goldSelector.options.length > 0) {
                updateChart();
            }

            // =================================================================
            // 5. МОДАЛЬНОЕ ОКНО ДЕТАЛЕЙ ДАТЫ (ВСПЛЫВАЮЩЕЕ ОКНО)
            // =================================================================

            // Находим все элементы с атрибутом data-date
            const dateTriggers = document.querySelectorAll('.date-trigger');
            const modalTitle = document.getElementById('date-modal-title');
            const modalBody = document.getElementById('date-modal-body');
            const modalDownloadLink = document.getElementById('modal-download-link');
            const modalCheckbox = document.getElementById('date-modal'); // Ссылка на скрытый чекбокс DaisyUI

            dateTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault(); // <-- Блокируем переход по ссылке #

                    const date = this.dataset.date;
                    const items = allHistoricalPrices[date];

                    if (!items) return;

                    // Обновляем заголовок
                    const formattedDate = new Date(date).toLocaleDateString('ru-RU', {day: '2-digit', month: '2-digit', year: 'numeric'});
                    modalTitle.textContent = `Цены на ${formattedDate}`;

                    // Обновляем тело таблицы
                    let tableHtml = `
                        <div class="overflow-x-auto">
                            <table class="table table-compact w-full text-base">
                                <thead>
                                    <tr>
                                        <th>Вес (г)</th>
                                        <th class="text-right">Покупка</th>
                                        <th class="text-right">Продажа</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    items.forEach(price => {
                        tableHtml += `
                            <tr>
                                <td class="font-bold">${goldItems[price.gold_id].name}</td>
                                <td class="text-right">${formatSom(price.buy_in_kopecks)} сом</td>
                                <td class="text-right">${formatSom(price.sale_kopecks)} сом</td>
                            </tr>
                        `;
                    });

                    tableHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    modalBody.innerHTML = tableHtml;

                    // Обновляем ссылку на "скачать"
                    const downloadData = items.map(price => ({
                        date: date,
                        weight: goldItems[price.gold_id].name,
                        buy: formatSom(price.buy_in_kopecks),
                        sale: formatSom(price.sale_kopecks)
                    }));

                    const csvContent = "data:text/csv;charset=utf-8," + encodeURIComponent(
                        "Дата;Вес (г);Покупка (сом);Продажа (сом)\n" +
                        downloadData.map(e => `${e.date};${e.weight};${e.buy.replace(/ /g, '')};${e.sale.replace(/ /g, '')}`).join("\n")
                    );

                    modalDownloadLink.href = csvContent;
                    modalDownloadLink.download = `gold_prices_${date}.csv`;

                    // Открываем модальное окно (используя DaisyUI/Tailwind trick)
                    modalCheckbox.checked = true;
                });
            });

        });
    </script>

    <div class="max-w-7xl mx-auto my-4 px-4 sm:px-6 lg:px-8 min-h-screen">

        {{-- 1. Приветствие, Описание и последняя дата обновления (УЛУЧШЕННОЕ SEO) --}}
        <header class="text-center py-12 bg-base-100 rounded-xl shadow-2xl mb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-primary mb-4">
                Слитки золота {{ env('APP_NAME') }}
            </h1>
            <p class="text-lg max-w-3xl mx-auto text-base-content/80">
                **Ваш надежный ресурс для инвестиций в золото в Кыргызстане.** Мы предоставляем актуальные цены на мерные золотые слитки, графики динамики и удобные калькуляторы для мгновенного расчета стоимости и потенциальной прибыли.
            </p>
            @if($latestPublicDate)
                <div class="badge badge-lg badge-neutral mt-4 shadow-md">
                    Последнее обновление цен: {{ \Carbon\Carbon::parse($latestPublicDate)->format('d.m.Y') }}
                </div>
            @endif
        </header>

        {{-- 2. Секция с последними ценами --}}
        <section class="mb-12">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Последние цены продажи</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6">
                @forelse($latestPrices as $price)
                    <div class="card bg-base-200 shadow-xl border border-base-300 transform hover:scale-[1.03] transition duration-300">
                        <div class="card-body p-4 sm:p-5 text-center">
                            <h3 class="text-lg sm:text-xl font-bold text-base-content mb-1 whitespace-nowrap">{{ $price->gold->name ?? 'N/A' }} г</h3>
                            <p class="text-xl sm:text-2xl font-extrabold text-primary">
                                {{ number_format($price->sale_kopecks / 100, 2, '.', ' ') }} <span class="text-sm font-normal">сом</span>
                            </p>
                            @php
                                $diff = $price->difference_sale_kopecks ?? 0;
                                $absDiff = abs($diff) / 100;
                                $colorClass = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-error' : 'text-base-content/60');
                                $icon = $diff > 0 ? '▲' : ($diff < 0 ? '▼' : '—');
                            @endphp
                            <span class="text-sm font-medium {{ $colorClass }} mt-1 whitespace-nowrap">
                                {{ $icon }} {{ number_format($absDiff, 2, '.', ' ') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="alert col-span-full alert-warning shadow-lg">Нет данных о ценах.</div>
                @endforelse
            </div>
        </section>

        ---

        {{-- 3. График динамики цен --}}
        <section class="mb-12 bg-base-100 p-6 rounded-xl shadow-2xl border border-base-300">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Динамика цен (График)</h2>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-6">
                <label for="gold-selector" class="font-medium whitespace-nowrap">Выберите слиток:</label>
                <select id="gold-selector" class="select select-bordered w-full sm:w-1/2 md:w-1/4 bg-base-200">
                    @foreach($golds as $gold)
                        <option value="{{ $gold->id }}" @if($gold->id === 1) selected @endif>
                            {{ $gold->name }} г
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="relative h-64 md:h-96">
                <canvas id="priceChart"></canvas>
            </div>
        </section>

        ---

        {{-- 4. Секция Калькуляторов (3 колонки) --}}
        <section class="mb-12">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Ваши помощники и расчеты</h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- 4.1. Калькулятор "Куда вложить?" (По сумме) --}}
                <div class="card bg-base-100 shadow-2xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-bold text-secondary">1. Куда вложить? (Совет)</h3>
                        <p class="text-base-content/70 mb-4 text-sm">Введите бюджет и получите 3 выгодных варианта покупки слитков.</p>

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
                            {{-- Ответы генерируются JS --}}
                        </div>
                    </div>
                </div>

                {{-- 4.2. Калькулятор "Сколько стоит?" (По количеству) --}}
                <div class="card bg-base-100 shadow-2xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-bold text-secondary">2. Сколько стоит? (Сумма)</h3>
                        <p class="text-base-content/70 mb-4 text-sm">Укажите количество слитков каждого веса для расчета общей стоимости.</p>

                        <div class="grid grid-cols-2 gap-4 max-h-52 overflow-y-auto pr-2">
                            @foreach($golds as $gold)
                                <label class="form-control">
                                    <div class="label p-0">
                                        <span class="label-text text-sm">{{ $gold->name }} г</span>
                                    </div>
                                    <input
                                        type="number"
                                        data-gold-id="{{ $gold->id }}"
                                        placeholder="0 шт."
                                        class="input input-bordered input-sm w-full quantity-input bg-base-200"
                                        min="0"
                                    />
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 p-4 bg-base-200 rounded-lg shadow-inner">
                            <p id="total-cost-output" class="text-lg font-extrabold text-primary text-center">
                                Введите количество слитков для расчета.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 4.3. Калькулятор "Доход/Убыток" (По граммам) --}}
                <div class="card bg-base-100 shadow-2xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-bold text-secondary">3. Моя прибыль</h3>
                        <p class="text-base-content/70 mb-4 text-sm">Сравните цену покупки (историческую) с текущей ценой продажи.</p>

                        {{-- ПОЛЕ ВВОДА ГРАММ --}}
                        <label class="form-control w-full">
                            <div class="label p-0">
                                <span class="label-text">Количество золота (грамм)</span>
                            </div>
                            <input
                                id="custom-gram-input"
                                type="number"
                                placeholder="Например, 10"
                                class="input input-bordered w-full bg-base-200"
                                min="0.01"
                                step="0.01"
                            />
                        </label>

                        {{-- ВЫБОР ДАТЫ --}}
                        <label class="form-control w-full mt-2">
                            <div class="label p-0">
                                <span class="label-text">Дата покупки</span>
                            </div>
                            <select id="purchase-date-select" class="select select-bordered w-full bg-base-200">
                                @php
                                    // 1. Получаем все уникальные даты из allHistoricalPrices.
                                    // Используем is_object() и toArray() для безопасного извлечения ключей из коллекции Laravel.
                                    $availableDates = [];
                                    if (is_object($allHistoricalPrices) && method_exists($allHistoricalPrices, 'toArray')) {
                                        $availableDates = array_keys($allHistoricalPrices->toArray());
                                    } elseif (is_array($allHistoricalPrices)) {
                                        $availableDates = array_keys($allHistoricalPrices);
                                    }

                                    // 2. УСИЛЕННАЯ ФИЛЬТРАЦИЯ: Оставляем только строки, похожие на YYYY-MM-DD
                                    $availableDates = array_filter($availableDates, function($date) {
                                        return is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
                                    });

                                    // 3. Сортируем в обратном порядке (самые новые сверху)
                                    rsort($availableDates);
                                @endphp
                                @forelse($availableDates as $dateStr)
                                    <option value="{{ $dateStr }}">
                                        {{ \Carbon\Carbon::parse($dateStr)->format('d.m.Y') }}
                                    </option>
                                @empty
                                    <option value="" disabled selected>Нет доступных дат</option>
                                @endforelse
                            </select>
                        </label>

                        {{-- ВЫБОР ВЕСА СЛИТКА --}}
                        <label class="form-control w-full mt-2">
                            <div class="label p-0">
                                <span class="label-text">Вес слитка на момент покупки</span>
                            </div>
                            <select id="purchase-gold-select" class="select select-bordered w-full bg-base-200">
                                @foreach($golds as $gold)
                                    <option value="{{ $gold->id }}" @if($gold->id === 1) selected @endif>
                                        {{ $gold->name }} г
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        {{-- Поле для отображения найденной исторической цены и ошибок --}}
                        <div id="historical-price-display" class="mt-2 p-2 bg-base-300 rounded-md text-sm text-base-content/80 font-medium">
                            Историческая цена (продажи): <span class="font-bold text-primary">0.00 сом</span>
                        </div>

                        {{-- КНОПКА РАСЧЕТА --}}
                        <button id="calculate-profit-button" class="btn btn-primary mt-4 disabled:opacity-50" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.828l.897-.897a.75.75 0 011.06 0l.897.897zm-5.657-4.243l.897-.897a.75.75 0 011.06 0l.897.897zM2.25 12h19.5" />
                            </svg>
                            Рассчитать прибыль
                        </button>

                        <div id="profit-output" class="mt-6 p-2 text-center">
                            <span class="text-base-content/70">Введите данные и нажмите "Рассчитать прибыль".</span>
                        </div>
                    </div>
                </div>


            </div>
        </section>

        ---

        {{-- 5. Секция Истории Цен с Пагинацией и Кликабельными Датами --}}
        <section class="mb-12 bg-base-100 p-6 rounded-xl shadow-2xl border border-base-300">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Архив цен на мерные слитки</h2>
            <p class="text-center text-sm mb-4 text-base-content/70">
                <span class="font-bold text-primary">Нажмите на дату</span> в таблице, чтобы посмотреть подробные цены за этот день и скачать данные.
            </p>

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
                        @php
                            $currentDate = '';
                        @endphp
                        @foreach($allPrices as $price)
                            <tr>
                                {{-- Дата (кликабельная) --}}
                                <td class="font-semibold whitespace-nowrap">
                                    @if($currentDate !== $price->public_date)
                                        @php $currentDate = $price->public_date; @endphp
                                        {{-- Атрибут href="#" и класс date-trigger используются для вызова модального окна через JS --}}
                                        <a href="#" class="date-trigger link link-hover link-primary font-bold" data-date="{{ $price->public_date }}">
                                            {{ \Carbon\Carbon::parse($price->public_date)->format('d.m.Y') }}
                                        </a>
                                    @endif
                                </td>

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

    {{-- Модальное окно для детального просмотра и скачивания цен за дату (DaisyUI использует скрытый input/label) --}}
    <input type="checkbox" id="date-modal" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box w-11/12 max-w-lg">
            <h3 id="date-modal-title" class="font-bold text-2xl text-primary mb-4">Цены на [Дата]</h3>

            <div id="date-modal-body" class="mb-4">
                {{-- Содержимое генерируется JS --}}
            </div>

            <div class="modal-action justify-between">
                {{-- Ссылка для скачивания (генерируется JS) --}}
                <a id="modal-download-link" href="#" class="btn btn-outline btn-success" download="gold_prices.csv">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Скачать CSV
                </a>
                <label for="date-modal" class="btn btn-primary">Закрыть</label>
            </div>
        </div>
        {{-- Фон, который закрывает модальное окно при клике --}}
        <label class="modal-backdrop" for="date-modal">Закрыть</label>
    </div>

@endsection
