@extends('public.layout.base')

@section('title', 'Мои золотые слитки')

@section('content')
<div class="max-w-7xl mx-auto my-2 py-8 sm:py-12 min-h-screen">
    <div class="drawer lg:drawer-open min-h-full">
        <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex flex-col">
            <div class="lg:hidden px-4 mb-4">
                <label for="my-drawer-2" class="btn bg-base-100 drawer-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    Меню
                </label>
            </div>

            <div class="flex-grow p-6 sm:p-10 bg-base-100 rounded-box shadow-xl mx-4 lg:ml-0 lg:mr-0">
                <h1 class="text-3xl font-bold mb-6 text-gray-800">Мои золотые слитки</h1>

                @if($goldBars->isNotEmpty())
                    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
                        <h2 class="text-2xl font-semibold mb-4">Добавить новую запись</h2>
                        <form action="{{ route('my-gold.store') }}" method="POST" id="gold-form">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="gold_bar_id" class="block text-sm font-medium text-gray-700">Вес слитка</label>
                                    <select id="gold_bar_id" name="gold_bar_id" required class="mt-1 block w-full">
                                        @foreach($goldBars as $goldBar)
                                            <option value="{{ $goldBar->id }}">{{ $goldBar->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="purchase_date" class="block text-sm font-medium text-gray-700">Дата покупки</label>
                                    <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">Количество</label>
                                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <label for="comment" class="block text-sm font-medium text-gray-700">Комментарий</label>
                                    <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('comment') }}</textarea>
                                </div>
                            </div>
                            <div class="mt-6 text-right">
                                <button type="submit" id="submit-button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Сохранить
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg shadow-md mb-8" role="alert">
                        <p class="font-bold">Функция неактивна</p>
                        <p>Для добавления личных слитков в систему сначала необходимо добавить информацию о видах слитков.</p>
                    </div>
                @endif

                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4">Ваши активы</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Слиток</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата покупки</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Кол-во</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена покупки</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Текущая цена</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Разница (за шт.)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Общая разница</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($userBars as $bar)
                                    @php
                                        $currentPriceRecord = $currentGoldPrices[$bar->gold_bar_id] ?? null;
                                        $currentPrice = $currentPriceRecord ? ($currentPriceRecord->buy_in_kopecks / 100) : 0;
                                        $priceDifference = $currentPrice - $bar->purchase_price_per_bar;
                                        $totalDifference = $priceDifference * $bar->quantity;
                                        $colorClass = $priceDifference >= 0 ? 'text-green-600' : 'text-red-600';
                                        $sign = $priceDifference >= 0 ? '+' : '';
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $bar->goldBar->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $bar->purchase_date->format('d.m.Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $bar->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>{{ number_format($bar->purchase_price_per_bar, 2, ',', ' ') }}</div>
                                            @if($bar->price_date)
                                                <div class="text-xs text-gray-500">(на {{ $bar->price_date->format('d.m.Y') }})</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ number_format($currentPrice, 2, ',', ' ') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold {{ $colorClass }}">
                                            {{ $sign }}{{ number_format($priceDifference, 2, ',', ' ') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold {{ $colorClass }}">
                                            {{ $sign }}{{ number_format($totalDifference, 2, ',', ' ') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('my-gold.edit', $bar->id) }}" class="text-indigo-600 hover:text-indigo-900">Изменить</a>
                                            <form action="{{ route('my-gold.destroy', $bar->id) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Вы уверены, что хотите удалить эту запись?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Удалить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">У вас пока нет добавленных слитков.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @include('client.component.left_navbar')
    </div>
</div>

<script>
    const goldForm = document.getElementById('gold-form');
    if (goldForm) {
        goldForm.addEventListener('submit', function() {
            const button = document.getElementById('submit-button');
            button.disabled = true;
            button.innerText = 'Сохранение...';
        });
    }
</script>
@endsection
