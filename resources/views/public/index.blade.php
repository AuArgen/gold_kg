@extends('public.layout.base')

@section('title', 'Алтын куймаларынын актуалдуу баалары | Инвестициялык калькулятор')
@section('description', 'Кыргызстандагы алтын куймаларынын эң акыркы баалары. Баалардын динамикасы, пайданы эсептөө калькуляторлору жана инвестиция боюнча кеңештер.')

@section('content')
    {{-- CDN Chart.js үчүн (график тартуу үчүн) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latestPrices = @json($latestPrices);
            const goldItems = @json($golds->keyBy('id'));
            // !!! МААНИЛҮҮ: allHistoricalPrices JSON объект катары берилет. !!!
            const allHistoricalPrices = @json($allHistoricalPrices);

            // Пайда калькуляторунда салыштыруу үчүн акыркы датаны алабыз
            const latestDate = '{{ \Carbon\Carbon::parse($latestPublicDate)->format('Y-m-d') }}';
            const latestPricesMap = new Map();
            latestPrices.forEach(p => latestPricesMap.set(p.gold_id, p));

            // Сандарды форматтоо функциясы (миңдиктерди боштук менен бөлүү)
            function formatSom(kopecks) {
                if (kopecks === undefined || kopecks === null) return '0.00';
                return (kopecks / 100).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
            }

            // =================================================================
            // 1.1. "АКЫРКЫ БААЛАРДЫ КӨЧҮРҮҮ" БАСКЫЧЫ
            // =================================================================
            const copyLatestBtn = document.getElementById('copy-latest-btn');
            if (copyLatestBtn) {
                copyLatestBtn.addEventListener('click', function() {
                    if (!latestPrices || latestPrices.length === 0) return;

                    // Бааларды форматтоо
                    const textToCopy = latestPrices.map(p => {
                        const weight = goldItems[p.gold_id] ? goldItems[p.gold_id].name : 'N/A';
                        const price = formatSom(p.sale_kopecks);
                        return `${weight}г: ${price} сом`;
                    }).join('\n');

                    const dateStr = '{{ \Carbon\Carbon::parse($latestPublicDate)->format('d.m.Y') }}';
                    const fullText = `Алтын баалары (${dateStr}):\n\n${textToCopy}`;

                    navigator.clipboard.writeText(fullText).then(() => {
                        const originalContent = copyLatestBtn.innerHTML;
                        copyLatestBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Көчүрүлдү!
                        `;
                        copyLatestBtn.classList.remove('btn-outline', 'btn-primary');
                        copyLatestBtn.classList.add('btn-success', 'text-white');

                        setTimeout(() => {
                            copyLatestBtn.innerHTML = originalContent;
                            copyLatestBtn.classList.remove('btn-success', 'text-white');
                            copyLatestBtn.classList.add('btn-outline', 'btn-primary');
                        }, 2000);
                    }).catch(err => {
                        console.error('Көчүрүүдө ката кетти: ', err);
                    });
                });
            }

            // =================================================================
            // 1.2. ТЕЗ ЭСЕПТЕГИЧ (QUICK CALCULATOR)
            // =================================================================
            window.openQuickCalc = function(goldId, priceKopecks, goldName) {
                const modal = document.getElementById('quick-calc-modal');
                const title = document.getElementById('qc-title');
                const priceDisplay = document.getElementById('qc-price-display');
                const qtyInput = document.getElementById('qc-qty');
                const budgetInput = document.getElementById('qc-budget');
                const resultDisplay = document.getElementById('qc-result');

                title.textContent = `${goldName}г куймасын эсептөө`;
                priceDisplay.textContent = `${formatSom(priceKopecks)} сом`;

                qtyInput.value = '';
                budgetInput.value = '';
                resultDisplay.innerHTML = '<span class="text-base-content/60">Эсептөө үчүн санын же сумманы жазыңыз</span>';

                function calculate() {
                    const price = priceKopecks / 100;

                    if (document.activeElement === qtyInput && qtyInput.value) {
                        budgetInput.value = '';
                        const qty = parseFloat(qtyInput.value);
                        if (qty > 0) {
                            const total = qty * price;
                            resultDisplay.innerHTML = `
                                <div class="text-center">
                                    <p class="text-sm">Жалпы баасы:</p>
                                    <p class="text-3xl font-bold text-primary">${formatSom(total * 100)} сом</p>
                                </div>
                            `;
                        }
                    }
                    else if (document.activeElement === budgetInput && budgetInput.value) {
                        qtyInput.value = '';
                        const budget = parseFloat(budgetInput.value);
                        if (budget > 0) {
                            const count = Math.floor(budget / price);
                            const totalCost = count * price;
                            const remainder = budget - totalCost;

                            if (count > 0) {
                                resultDisplay.innerHTML = `
                                    <div class="text-center">
                                        <p class="text-sm">Сиздин акчага келет:</p>
                                        <p class="text-3xl font-bold text-success">${count} даана</p>
                                        <p class="text-xs text-base-content/70 mt-1">Калдык: ${formatSom(remainder * 100)} сом</p>
                                    </div>
                                `;
                            } else {
                                resultDisplay.innerHTML = `<span class="text-error">Бул акчага 1 даана да келбейт.</span>`;
                            }
                        }
                    }
                }

                qtyInput.oninput = calculate;
                budgetInput.oninput = calculate;

                document.getElementById('quick-calc-checkbox').checked = true;
                setTimeout(() => qtyInput.focus(), 100);
            };


            // =================================================================
            // 1. "БААСЫ КАНЧА?" КАЛЬКУЛЯТОРУ (Куймалардын саны боюнча)
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
                            totalKopecks += priceData.sale_kopecks * quantity;
                        }
                    }
                });

                if (hasInput) {
                    totalCostOutput.innerHTML = `Жалпы баасы: <span class="text-primary font-bold">${formatSom(totalKopecks)} сом</span>`;
                } else {
                    totalCostOutput.innerHTML = '<span class="text-base-content/60">Санын жазыңыз...</span>';
                }
            }

            quantityInputs.forEach(input => {
                input.addEventListener('input', calculateTotalCost);
            });


            // =================================================================
            // 2. "КАЙДА ИНВЕСТИЦИЯ КЫЛУУ КЕРЕК?" КАЛЬКУЛЯТОРУ (Сумма боюнча)
            // =================================================================
            const budgetInput = document.getElementById('budget-input');
            const investmentAdvice = document.getElementById('investment-advice');

            function getAdvice(budget) {
                if (budget <= 0) return [{ text: "Кеңеш алуу үчүн сумманы жазыңыз.", type: 'info' }];

                const sortedPrices = latestPrices
                    .map(p => ({
                        ...p,
                        weightVal: parseFloat(goldItems[p.gold_id].name.replace(/[^0-9.]/g, '')),
                        weightName: goldItems[p.gold_id].name,
                        priceSom: p.sale_kopecks / 100
                    }))
                    .sort((a, b) => b.weightVal - a.weightVal);

                if (sortedPrices.length === 0 || sortedPrices[sortedPrices.length - 1].priceSom > budget) {
                    return [{ text: "Сиздин бюджетиңиз эң кичинекей куйманы алууга да жетпейт.", type: 'warning' }];
                }

                const adviceList = [];

                // --- ВАРИАНТ 1: Эң көп алтын (Greedy Algorithm) ---
                let tempBudget1 = budget;
                let basket1 = [];
                let totalCost1 = 0;

                for (let item of sortedPrices) {
                    if (tempBudget1 >= item.priceSom) {
                        const count = Math.floor(tempBudget1 / item.priceSom);
                        if (count > 0) {
                            basket1.push(`${count} даана ${item.weightName}г`);
                            totalCost1 += count * item.priceSom;
                            tempBudget1 -= count * item.priceSom;
                        }
                    }
                }

                if (basket1.length > 0) {
                    adviceList.push({
                        title: "1-вариант: Эң көп алтын (Үнөмдүү)",
                        text: `Бул акчага эң көп салмактагы алтын алуу үчүн: **${basket1.join(', ')}** алыңыз.`,
                        subtext: `Жалпы баасы: ${formatSom(totalCost1 * 100)} сом. Калдык: ${formatSom(tempBudget1 * 100)} сом.`,
                        type: 'success'
                    });
                }

                // --- ВАРИАНТ 2: Ири салым (Бир чоң куйма) ---
                const largestSingle = sortedPrices.find(p => p.priceSom <= budget);
                if (largestSingle) {
                    const isDuplicate = basket1.length === 1 && basket1[0].startsWith("1 даана") && basket1[0].includes(`${largestSingle.weightName}г`);

                    if (!isDuplicate) {
                        const remainder = budget - largestSingle.priceSom;
                        adviceList.push({
                            title: "2-вариант: Ири салым",
                            text: `Майдалабай, бир чоң куйма алыңыз: **1 даана ${largestSingle.weightName}г**.`,
                            subtext: `Баасы: ${formatSom(largestSingle.priceSom * 100)} сом. Калдык: ${formatSom(remainder * 100)} сом.`,
                            type: 'info'
                        });
                    }
                }

                // --- ВАРИАНТ 3: Ликвиддүүлүк (Майдалап алуу) ---
                const liquidPrices = sortedPrices.filter(p => p.weightVal <= 10).sort((a, b) => b.weightVal - a.weightVal);

                if (liquidPrices.length > 0) {
                    let tempBudget3 = budget;
                    let basket3 = [];
                    let totalCost3 = 0;

                    for (let item of liquidPrices) {
                         if (tempBudget3 >= item.priceSom) {
                            const count = Math.floor(tempBudget3 / item.priceSom);
                            if (count > 0) {
                                basket3.push(`${count} даана ${item.weightName}г`);
                                totalCost3 += count * item.priceSom;
                                tempBudget3 -= count * item.priceSom;
                            }
                        }
                    }

                    const basket1Str = basket1.join(', ');
                    const basket3Str = basket3.join(', ');

                    if (basket3.length > 0 && basket1Str !== basket3Str) {
                         adviceList.push({
                            title: "3-вариант: Ликвиддүү (Бөлүп сатууга ыңгайлуу)",
                            text: `Кийин бөлүп сатуу үчүн майда куймаларды алыңыз: **${basket3Str}**.`,
                            subtext: `Жалпы баасы: ${formatSom(totalCost3 * 100)} сом. Калдык: ${formatSom(tempBudget3 * 100)} сом.`,
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

                if (budget > 0 && advice.length === 0) {
                     investmentAdvice.innerHTML = '<div class="alert alert-warning shadow-lg">Бул акчага ылайыктуу варианттар табылган жок.</div>';
                     return;
                }

                advice.forEach(item => {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${item.type} shadow-lg mb-3 flex flex-col items-start text-left`;

                    const title = item.title || 'Кеңеш';
                    const text = item.text || '';
                    const subtext = item.subtext || '';

                    alertDiv.innerHTML = `
                        <div class="flex items-center gap-2 w-full">
                             <span class="font-bold text-lg underline decoration-dashed">${title}</span>
                        </div>
                        <div class="mt-1">
                            <p class="text-base">${text.replace(/\*\*/g, '<strong>').replace(/\*\*/g, '</strong>')}</p>
                            <p class="text-sm opacity-80 mt-1 font-mono">${subtext}</p>
                        </div>
                    `;
                    investmentAdvice.appendChild(alertDiv);
                });
            }

            budgetInput.addEventListener('input', updateAdvice);


            // =================================================================
            // 3. ПАЙДА КАЛЬКУЛЯТОРУ (Грамм боюнча)
            // =================================================================

            // 1г сатып алуунун учурдагы баасы (пайданы эсептөө үчүн)
            const currentGramPriceK = latestPricesMap.get(1)?.buy_in_kopecks;

            // Форма элементтери
            const customGramInput = document.getElementById('custom-gram-input');
            const purchaseDateSelect = document.getElementById('purchase-date-select');
            const purchaseGoldSelect = document.getElementById('purchase-gold-select');
            const calculateProfitButton = document.getElementById('calculate-profit-button');
            const profitOutput = document.getElementById('profit-output');
            const historicalPriceDisplay = document.getElementById('historical-price-display'); // Тарыхый бааны көрсөтүү үчүн


            // -----------------------------------------------------------------
            // 3.1. Тарыхый бааны издөө жана көрсөтүү функциясы (UI)
            // -----------------------------------------------------------------
            function updateHistoricalPriceUI() {
                const selectedDate = purchaseDateSelect.value;
                const selectedGoldId = parseInt(purchaseGoldSelect.value);

                let historicalPriceK = null;
                let ingotWeightDisplay = '';

                if (allHistoricalPrices[selectedDate]) {
                    const items = allHistoricalPrices[selectedDate];
                    const targetItem = items.find(p => p.gold_id === selectedGoldId);

                    if (targetItem) {
                        // Банк саткан баа (Sale Price) - бул колдонуучу үчүн сатып алуу баасы
                        historicalPriceK = targetItem.sale_kopecks;
                        // Куйманын атынан салмакты алабыз (мисалы, "1 г" -> "1")
                        ingotWeightDisplay = goldItems[selectedGoldId]?.name.replace(/[^0-9.]/g, '') || '1';
                    }
                }

                if (historicalPriceK !== null && historicalPriceK > 0) {
                    historicalPriceDisplay.innerHTML = `Тарыхый баа (сатуу): <span class="font-bold text-primary">${formatSom(historicalPriceK)} сом / ${ingotWeightDisplay} г</span>`;
                    historicalPriceDisplay.classList.remove('text-error', 'text-warning');
                    historicalPriceDisplay.classList.add('text-base-content/80');
                    calculateProfitButton.disabled = false;
                } else {
                    historicalPriceDisplay.innerHTML = `
                        <span class="font-bold text-error">
                            ${selectedDate ? new Date(selectedDate).toLocaleDateString('ru-RU', {day: '2-digit', month: '2-digit', year: 'numeric'}) : 'Тандалган дата'}
                            үчүн ${goldItems[selectedGoldId]?.name || 'N/A'}г баасы жок.
                        </span>
                    `;
                    historicalPriceDisplay.classList.remove('text-base-content/80', 'text-warning');
                    historicalPriceDisplay.classList.add('text-error');
                    // Баа жок болсо, эсептөө баскычын өчүрөбүз
                    calculateProfitButton.disabled = true;
                    profitOutput.innerHTML = '<span class="text-base-content/70">Маалыматтарды киргизип, "Пайданы эсептөө" баскычын басыңыз.</span>';
                }
            }


            // -----------------------------------------------------------------
            // 3.2. Пайданы эсептөө функциясы (Баскычты басканда)
            // -----------------------------------------------------------------
            function calculateProfit() {
                // Чыгууну тазалоо
                profitOutput.innerHTML = '';

                const grams = parseFloat(customGramInput.value) || 0;
                const selectedDate = purchaseDateSelect.value;
                const selectedGoldId = parseInt(purchaseGoldSelect.value);

                if (grams <= 0) {
                    profitOutput.innerHTML = '<div class="alert alert-warning shadow-lg text-sm">Сураныч, алтындын салмагын грамм менен жазыңыз.</div>';
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
                    profitOutput.innerHTML = '<div class="alert alert-error shadow-lg text-sm">Ката: Тарыхый баа же учурдагы 1г баасы жок.</div>';
                    return;
                }

                // Тандалган куйманын салмагын алабыз (сан түрүндө)
                const ingotWeight = parseFloat(goldItems[selectedGoldId]?.name.replace(/[^0-9.]/g, '')) || 1;

                // 1. Фактылуу сатып алуу баасын эсептейбиз
                // Тандалган куйманын баасы / Куйманын салмагы * жалпы грамм
                const totalCostK = (historicalPriceK / ingotWeight) * grams;

                // 2. Учурдагы сатуу баасын эсептейбиз
                // 1г сатып алуунун учурдагы баасы * грамм саны
                const currentValueK = currentGramPriceK * grams;

                const profitK = currentValueK - totalCostK;

                const profitSom = formatSom(profitK);
                const costSom = formatSom(totalCostK);
                const type = profitK >= 0 ? 'text-success' : 'text-error';
                const icon = profitK >= 0 ? '▲' : '▼';
                const profitLabel = profitK >= 0 ? 'КИРЕШЕ' : 'ЧЫГЫМ';

                profitOutput.innerHTML = `
                    <div class="flex flex-col space-y-2 p-4 bg-base-300 rounded-lg shadow-md">
                        <p class="text-sm text-base-content font-medium">Сиздин сатып алуу бааңыз: <span class="font-extrabold text-secondary">${costSom} сом</span></p>
                        <p class="text-sm text-base-content font-medium">Учурдагы сатуу баасы: <span class="font-extrabold text-primary">${formatSom(currentValueK)} сом</span></p>
                        <p class="text-lg ${type} font-bold border-t border-base-content/30 pt-2 mt-2">
                            Сиздин болжолдуу ${profitLabel}: <span class="text-2xl">${icon} ${profitSom} сом</span>
                        </p>
                    </div>
                `;
            }

            // -----------------------------------------------------------------
            // 3.3. Окуяларды байлоо
            // -----------------------------------------------------------------
            purchaseDateSelect.addEventListener('change', updateHistoricalPriceUI);
            purchaseGoldSelect.addEventListener('change', updateHistoricalPriceUI);
            customGramInput.addEventListener('input', updateHistoricalPriceUI); // Баскычты активдештирүү үчүн

            calculateProfitButton.addEventListener('click', calculateProfit);

            // Инициализация
            updateHistoricalPriceUI();


            // =================================================================
            // 4. БААЛАРДЫН ДИНАМИКАСЫ (ГРАФИК)
            // =================================================================
            const chartCanvas = document.getElementById('priceChart');
            const goldSelector = document.getElementById('gold-selector');
            let priceChartInstance;

            function generateChartData(selectedGoldId) {
                const dates = [];
                const prices = [];

                // Объектти массивге айландырып, дата боюнча сорттойбуз (эски -> жаңы)
                const sortedDates = Object.keys(allHistoricalPrices).sort();

                sortedDates.forEach(dateStr => {
                    const items = allHistoricalPrices[dateStr];
                    const targetItem = items.find(p => p.gold_id === parseInt(selectedGoldId));

                    if (targetItem) {
                        // График үчүн датаны форматтоо
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
                            label: `${goldItems[selectedGoldId].name}г сатуу баасы (сом)`,
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
                                    text: 'Баа (сом)'
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

            // Жүктөлгөндө графикти инициализациялоо
            if (goldSelector.options.length > 0) {
                updateChart();
            }

            // =================================================================
            // 5. ДАТА БОЮНЧА ТОЛУК МААЛЫМАТ (МОДАЛДЫК ТЕРЕЗЕ)
            // =================================================================

            // data-date атрибуту бар бардык элементтерди табабыз
            const dateTriggers = document.querySelectorAll('.date-trigger');
            const modalTitle = document.getElementById('date-modal-title');
            const modalBody = document.getElementById('date-modal-body');
            const modalDownloadLink = document.getElementById('modal-download-link');
            const modalCopyButton = document.getElementById('modal-copy-button'); // Жаңы баскыч
            const modalCheckbox = document.getElementById('date-modal'); // DaisyUI жашыруун чекбокс

            dateTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault(); // <-- # шилтемесине өтүүнү бөгөттөө

                    const date = this.dataset.date;
                    const items = allHistoricalPrices[date];

                    if (!items) return;

                    // Заголовокту жаңыртуу
                    const formattedDate = new Date(date).toLocaleDateString('ru-RU', {day: '2-digit', month: '2-digit', year: 'numeric'});
                    modalTitle.textContent = `${formattedDate} үчүн баалар`;

                    // Таблицанын денесин жаңыртуу
                    let tableHtml = `
                        <div class="overflow-x-auto">
                            <table class="table table-compact w-full text-base">
                                <thead>
                                    <tr>
                                        <th>Салмак (г)</th>
                                        <th class="text-right">Сатып алуу</th>
                                        <th class="text-right">Сатуу</th>
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

                    // "Жүктөө" шилтемесин жаңыртуу
                    const downloadData = items.map(price => ({
                        date: date,
                        weight: goldItems[price.gold_id].name,
                        buy: formatSom(price.buy_in_kopecks),
                        sale: formatSom(price.sale_kopecks)
                    }));

                    const csvContent = "data:text/csv;charset=utf-8," + encodeURIComponent(
                        "Дата;Салмак (г);Сатып алуу (сом);Сатуу (сом)\n" +
                        downloadData.map(e => `${e.date};${e.weight};${e.buy.replace(/ /g, '')};${e.sale.replace(/ /g, '')}`).join("\n")
                    );

                    modalDownloadLink.href = csvContent;
                    modalDownloadLink.download = `gold_prices_${date}.csv`;

                    // "Көчүрүү" баскычынын логикасы
                    modalCopyButton.onclick = function() {
                        const textToCopy = items.map(price => {
                            const weight = goldItems[price.gold_id].name;
                            const buy = formatSom(price.buy_in_kopecks);
                            const sale = formatSom(price.sale_kopecks);
                            return `${weight}г: Сатып алуу ${buy} сом, Сатуу ${sale} сом`;
                        }).join('\n');

                        const fullText = `Алтын баалары (${formattedDate}):\n\n${textToCopy}`;

                        navigator.clipboard.writeText(fullText).then(() => {
                            // Ийгиликтүү көчүрүлдү деген билдирүү (мисалы, баскычтын текстин өзгөртүү)
                            const originalText = modalCopyButton.innerHTML;
                            modalCopyButton.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Көчүрүлдү!
                            `;
                            modalCopyButton.classList.add('btn-success');
                            modalCopyButton.classList.remove('btn-outline');

                            setTimeout(() => {
                                modalCopyButton.innerHTML = originalText;
                                modalCopyButton.classList.remove('btn-success');
                                modalCopyButton.classList.add('btn-outline');
                            }, 2000);
                        }).catch(err => {
                            console.error('Көчүрүүдө ката кетти: ', err);
                        });
                    };

                    // Модалдык терезени ачуу
                    modalCheckbox.checked = true;
                });
            });

        });
    </script>

    <div class="max-w-7xl mx-auto my-4 px-4 sm:px-6 lg:px-8 min-h-screen">

        {{-- 1. Саламдашуу, Сүрөттөмө жана акыркы жаңыртуу датасы --}}
        <header class="text-center py-12 bg-base-100 rounded-xl shadow-2xl mb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-primary mb-4">
                {{ env('APP_NAME') }} - Кыргызстандагы алтын куймалары
            </h1>
            <div class="text-lg max-w-4xl mx-auto text-base-content/80 space-y-4">
                <p>
                    **Бул сайт – Кыргызстандагы алтынга инвестиция кылууну каалагандар үчүн ишенимдүү жардамчы.**
                    Биз Улуттук банктын мерные (өлчөнгөн) алтын куймаларынын эң актуалдуу бааларын сунуштайбыз.
                </p>
                <p class="text-base">
                    <strong>Сайттын мүмкүнчүлүктөрү:</strong>
                </p>
                <ul class="list-none space-y-2 text-base">
                    <li>📊 <strong>Бааларды көзөмөлдөө:</strong> Ар бир куйманын (1г дан 100г чейин) учурдагы сатуу жана сатып алуу бааларын көрүңүз.</li>
                    <li>📈 <strong>Аналитика:</strong> Интерактивдүү график аркылуу баалардын өсүү же төмөндөө тарыхын изилдеңиз.</li>
                    <li>🧮 <strong>Акылдуу калькуляторлор:</strong> Бюджетиңизге жараша эң пайдалуу куймаларды тандаңыз же мурунку сатып алууларыңыздын кирешесин эсептеңиз.</li>
                    <li>🗄️ <strong>Архив:</strong> Өткөн күндөрдүн бааларын карап чыгып, маалыматтарды CSV форматында жүктөп алыңыз.</li>
                </ul>
            </div>
            @if($latestPublicDate)
                <div class="badge badge-lg badge-neutral mt-6 shadow-md">
                    Баалар акыркы жолу жаңыртылды: {{ \Carbon\Carbon::parse($latestPublicDate)->format('d.m.Y') }}
                </div>
            @endif

            {{-- 1.1. ЖЕКЕ КАБИНЕТКЕ ЧАКЫРУУ БЛОГУ (ЖАҢЫ) --}}
            <div class="mt-8 max-w-2xl mx-auto">
                @if(Auth::check())
                    <div class="alert shadow-lg bg-base-200 border-l-4 border-primary text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-primary shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <h3 class="font-bold">Салам, {{ Auth::user()->name }}!</h3>
                            <div class="text-xs">Сиздин жеке кабинетиңизде алтын активдериңизди көзөмөлдөө мүмкүнчүлүгү бар.</div>
                        </div>
                        <a href="{{ route('my-gold.index') }}" class="btn btn-sm btn-primary">Кабинетке өтүү</a>
                    </div>
                @else
                    <div class="alert shadow-lg bg-base-200 border-l-4 border-secondary text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-secondary shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div>
                            <h3 class="font-bold">Жеке портфель түзүңүз!</h3>
                            <div class="text-xs">Катталып, өзүңүздүн алтын активдериңиздин кирешесин эсептеп туруңуз.</div>
                        </div>
                        <a href="{{ route('login') }}" class="btn btn-sm btn-secondary">Катталуу / Кирүү</a>
                    </div>
                @endif
            </div>

        </header>

        {{-- 2. Акыркы баалар бөлүмү --}}
        <section class="mb-12">
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-6">
                <h2 class="text-3xl font-bold text-base-content">Акыркы сатуу баалары</h2>
                <button id="copy-latest-btn" class="btn btn-sm btn-outline btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Көчүрүү
                </button>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6">
                @forelse($latestPrices as $price)
                    <div class="card bg-base-200 shadow-xl border border-base-300 transform hover:scale-[1.03] transition duration-300 relative group">
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

                            {{-- ТЕЗ ЭСЕПТӨӨ БАСКЫЧЫ (ОҢДОЛДУ: Дайыма көрүнөт) --}}
                            <button
                                onclick="openQuickCalc({{ $price->gold_id }}, {{ $price->sale_kopecks }}, '{{ $price->gold->name ?? '' }}')"
                                class="btn btn-sm btn-circle btn-ghost absolute top-2 right-2 text-base-content/40 hover:text-primary hover:bg-base-300 transition-colors tooltip tooltip-left"
                                data-tip="Тез эсептөө">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="alert col-span-full alert-warning shadow-lg">Баалар боюнча маалымат жок.</div>
                @endforelse
            </div>
        </section>

        ---

        {{-- 3. Баалардын динамикасы (График) --}}
        <section class="mb-12 bg-base-100 p-6 rounded-xl shadow-2xl border border-base-300">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Баалардын өзгөрүү динамикасы (График)</h2>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-6">
                <label for="gold-selector" class="font-medium whitespace-nowrap">Куйманы тандаңыз:</label>
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

        {{-- 4. Калькуляторлор бөлүмү (3 колонка) --}}
        <section class="mb-12">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Сиздин жардамчыларыңыз жана эсептөөлөр</h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- 4.1. "Кайда инвестиция кылуу керек?" (Сумма боюнча) --}}
                <div class="card bg-base-100 shadow-2xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-bold text-secondary">1. Кайда инвестиция кылуу керек?</h3>
                        <p class="text-base-content/70 mb-4 text-sm">Бюджетиңизди жазыңыз, биз сизге эң пайдалуу 3 вариантты сунуштайбыз.</p>

                        {{-- ЖЫЙЫНТЫК (ЖОГОРУГА ЖЫЛДЫРЫЛДЫ) --}}
                        <div id="investment-advice" class="mb-4 min-h-[60px]">
                            {{-- Жооптор JS аркылуу чыгат --}}
                            <div class="alert shadow-sm bg-base-200 text-base-content/60 text-sm">
                                Жооп алуу үчүн сумманы жазыңыз.
                            </div>
                        </div>

                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text">Сиздин бюджет (сом)</span>
                            </div>
                            <input
                                id="budget-input"
                                type="number"
                                placeholder="Мисалы, 60000"
                                class="input input-bordered w-full bg-base-200"
                                min="1"
                            />
                        </label>
                    </div>
                </div>

                {{-- 4.2. "Баасы канча?" (Саны боюнча) --}}
                <div class="card bg-base-100 shadow-2xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-bold text-secondary">2. Баасы канча? (Жалпы сумма)</h3>
                        <p class="text-base-content/70 mb-4 text-sm">Ар бир куйманын санын көрсөтүп, жалпы баасын эсептеңиз.</p>

                        {{-- ЖЫЙЫНТЫК (ЖОГОРУГА ЖЫЛДЫРЫЛДЫ) --}}
                        <div class="mb-4 p-4 bg-base-200 rounded-lg shadow-inner min-h-[60px] flex items-center justify-center">
                            <p id="total-cost-output" class="text-lg font-extrabold text-primary text-center">
                                <span class="text-base-content/60 text-sm font-normal">Санын жазыңыз...</span>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 max-h-52 overflow-y-auto pr-2">
                            @foreach($golds as $gold)
                                <label class="form-control">
                                    <div class="label p-0">
                                        <span class="label-text text-sm">{{ $gold->name }} г</span>
                                    </div>
                                    <input
                                        type="number"
                                        data-gold-id="{{ $gold->id }}"
                                        placeholder="0 даана"
                                        class="input input-bordered input-sm w-full quantity-input bg-base-200"
                                        min="0"
                                    />
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 4.3. "Киреше/Чыгым" (Грамм боюнча) --}}
                <div class="card bg-base-100 shadow-2xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-bold text-secondary">3. Менин кирешем</h3>
                        <p class="text-base-content/70 mb-4 text-sm">Сатып алуу баасын (тарыхый) учурдагы сатуу баасы менен салыштырыңыз.</p>

                        {{-- ЖЫЙЫНТЫК (ЖОГОРУГА ЖЫЛДЫРЫЛДЫ) --}}
                        <div id="profit-output" class="mb-4 min-h-[60px]">
                            <div class="alert shadow-sm bg-base-200 text-base-content/60 text-sm text-center">
                                Маалыматтарды киргизип, "Пайданы эсептөө" баскычын басыңыз.
                            </div>
                        </div>

                        {{-- ГРАММ ЖАЗУУ --}}
                        <label class="form-control w-full">
                            <div class="label p-0">
                                <span class="label-text">Алтындын саны (грамм)</span>
                            </div>
                            <input
                                id="custom-gram-input"
                                type="number"
                                placeholder="Мисалы, 10"
                                class="input input-bordered w-full bg-base-200"
                                min="0.01"
                                step="0.01"
                            />
                        </label>

                        {{-- ДАТАНЫ ТАНДОО --}}
                        <label class="form-control w-full mt-2">
                            <div class="label p-0">
                                <span class="label-text">Сатып алган күн</span>
                            </div>
                            <select id="purchase-date-select" class="select select-bordered w-full bg-base-200">
                                @php
                                    // 1. Бардык уникалдуу даталарды алабыз
                                    $availableDates = [];
                                    if (is_object($allHistoricalPrices) && method_exists($allHistoricalPrices, 'toArray')) {
                                        $availableDates = array_keys($allHistoricalPrices->toArray());
                                    } elseif (is_array($allHistoricalPrices)) {
                                        $availableDates = array_keys($allHistoricalPrices);
                                    }

                                    // 2. ФИЛЬТР: YYYY-MM-DD форматындагыларды гана калтырабыз
                                    $availableDates = array_filter($availableDates, function($date) {
                                        return is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
                                    });

                                    // 3. Тескери тартипте сорттоо (жаңылары үстүндө)
                                    rsort($availableDates);
                                @endphp
                                @forelse($availableDates as $dateStr)
                                    <option value="{{ $dateStr }}">
                                        {{ \Carbon\Carbon::parse($dateStr)->format('d.m.Y') }}
                                    </option>
                                @empty
                                    <option value="" disabled selected>Даталар жок</option>
                                @endforelse
                            </select>
                        </label>

                        {{-- КУЙМАНЫ ТАНДОО --}}
                        <label class="form-control w-full mt-2">
                            <div class="label p-0">
                                <span class="label-text">Сатып алуу учурундагы куйманын салмагы</span>
                            </div>
                            <select id="purchase-gold-select" class="select select-bordered w-full bg-base-200">
                                @foreach($golds as $gold)
                                    <option value="{{ $gold->id }}" @if($gold->id === 1) selected @endif>
                                        {{ $gold->name }} г
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        {{-- Табылган тарыхый бааны көрсөтүү --}}
                        <div id="historical-price-display" class="mt-2 p-2 bg-base-300 rounded-md text-sm text-base-content/80 font-medium">
                            Тарыхый баа (сатуу): <span class="font-bold text-primary">0.00 сом</span>
                        </div>

                        {{-- ЭСЕПТӨӨ БАСКЫЧЫ --}}
                        <button id="calculate-profit-button" class="btn btn-primary mt-4 disabled:opacity-50" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.828l.897-.897a.75.75 0 011.06 0l.897.897zm-5.657-4.243l.897-.897a.75.75 0 011.06 0l.897.897zM2.25 12h19.5" />
                            </svg>
                            Пайданы эсептөө
                        </button>

                        <div id="profit-output" class="mt-6 p-2 text-center">
                            <span class="text-base-content/70">Маалыматтарды киргизип, "Пайданы эсептөө" баскычын басыңыз.</span>
                        </div>
                    </div>
                </div>


            </div>
        </section>

        ---

        {{-- 5. Баалар архиви (Пагинация жана даталар менен) --}}
        <section class="mb-12 bg-base-100 p-6 rounded-xl shadow-2xl border border-base-300">
            <h2 class="text-3xl font-bold text-center mb-6 text-base-content">Алтын куймаларынын баалар архиви</h2>
            <p class="text-center text-sm mb-4 text-base-content/70">
                Таблицадагы <span class="font-bold text-primary">датаны басып</span>, ошол күнкү толук маалыматты көрүп, жүктөп алсаңыз болот.
            </p>

            @if($allPrices->isEmpty())
                <div class="alert alert-info shadow-lg">Алтын баалары боюнча маалымат табылган жок.</div>
            @else
                {{-- Адаптивдүү таблица --}}
                <div class="overflow-x-auto rounded-box border border-base-300 shadow-md">
                    <table class="table table-zebra w-full text-base">
                        <thead class="bg-base-200">
                        <tr class="text-base-content">
                            <th>Дата</th>
                            <th class="text-center">Салмак (гр)</th>
                            <th class="text-right">Сатып алуу (сом)</th>
                            <th class="text-right">Сатуу (сом)</th>
                            <th class="text-center md:table-cell hidden">Δ Сатып алуу</th>
                            <th class="text-center">Δ Сатуу</th>
                        </tr>
                        </thead>

                        <tbody>
                        @php
                            $currentDate = '';
                        @endphp
                        @foreach($allPrices as $price)
                            <tr>
                                {{-- Дата (басууга болот) --}}
                                <td class="font-semibold whitespace-nowrap">
                                    @if($currentDate !== $price->public_date)
                                        @php $currentDate = $price->public_date; @endphp
                                        <a href="#" class="date-trigger link link-hover link-primary font-bold" data-date="{{ $price->public_date }}">
                                            {{ \Carbon\Carbon::parse($price->public_date)->format('d.m.Y') }}
                                        </a>
                                    @endif
                                </td>

                                {{-- Салмак --}}
                                <td class="font-medium whitespace-nowrap text-center">
                                    {{ $price->gold->name ?? 'N/A' }} г
                                </td>

                                {{-- Сатып алуу баасы --}}
                                <td class="text-right whitespace-nowrap">
                                    {{ number_format($price->buy_in_kopecks / 100, 2, '.', ' ') }}
                                </td>

                                {{-- Сатуу баасы --}}
                                <td class="text-right whitespace-nowrap">
                                    {{ number_format($price->sale_kopecks / 100, 2, '.', ' ') }}
                                </td>

                                {{-- Сатып алуу айырмасы (Мобилдикте жашырылган) --}}
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

                                {{-- Сатуу айырмасы --}}
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

    {{-- Модалдык терезе (DaisyUI) --}}
    <input type="checkbox" id="date-modal" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box w-11/12 max-w-lg">
            <h3 id="date-modal-title" class="font-bold text-2xl text-primary mb-4">Баалар [Дата]</h3>

            <div id="date-modal-body" class="mb-4">
                {{-- Мазмуну JS аркылуу чыгат --}}
            </div>

            <div class="modal-action justify-between">
                {{-- Көчүрүү баскычы --}}
                <button id="modal-copy-button" class="btn btn-outline btn-info">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                    Көчүрүү
                </button>

                {{-- Жүктөө шилтемеси --}}
                <a id="modal-download-link" href="#" class="btn btn-outline btn-success" download="gold_prices.csv">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    CSV жүктөө
                </a>
                <label for="date-modal" class="btn btn-primary">Жабуу</label>
            </div>
        </div>
        {{-- Фонду басканда жабылуу --}}
        <label class="modal-backdrop" for="date-modal">Жабуу</label>
    </div>

    {{-- ЖАҢЫ МОДАЛ: ТЕЗ ЭСЕПТЕГИЧ (ОҢДОЛДУ: Жыйынтык жогоруда) --}}
    <input type="checkbox" id="quick-calc-checkbox" class="modal-toggle" />
    <div class="modal" role="dialog" id="quick-calc-modal">
        <div class="modal-box">
            <h3 id="qc-title" class="font-bold text-2xl text-center mb-2">Эсептөө</h3>
            <p class="text-center text-base-content/70 mb-4">1 даана баасы: <span id="qc-price-display" class="font-bold text-primary">0 сом</span></p>

            <div class="flex flex-col gap-4">
                {{-- Жыйынтык (Эң жогоруга жылдырылды) --}}
                <div id="qc-result" class="bg-base-200 p-4 rounded-xl min-h-[80px] flex items-center justify-center border border-base-300">
                    <span class="text-base-content/60">Эсептөө үчүн санын же сумманы жазыңыз</span>
                </div>

                {{-- Санын жазуу --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Канча даана аласыз?</span>
                    </label>
                    <input type="number" id="qc-qty" placeholder="Саны (шт)" class="input input-bordered w-full text-lg" min="1" />
                </div>

                <div class="divider my-0 text-xs">ЖЕ</div>

                {{-- Сумманы жазуу --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Канча акчаңыз бар? (сом)</span>
                    </label>
                    <input type="number" id="qc-budget" placeholder="Сумма (сом)" class="input input-bordered w-full text-lg" min="1" />
                </div>
            </div>

            <div class="modal-action">
                <label for="quick-calc-checkbox" class="btn">Жабуу</label>
            </div>
        </div>
        <label class="modal-backdrop" for="quick-calc-checkbox">Жабуу</label>
    </div>

@endsection
