@extends('layouts.client')

@section('content')
<div class="container mx-auto p-6 bg-gray-100">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Мои золотые слитки</h1>

    <!-- Форма добавления -->
    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
        <h2 class="text-2xl font-semibold mb-4">Добавить новую запись</h2>
        <form action="{{ route('my-gold.store') }}" method="POST" id="gold-form">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700">Дата покупки</label>
                    <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('purchase_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Количество слитков</label>
                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('quantity')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-3">
                    <label for="comment" class="block text-sm font-medium text-gray-700">Комментарий (необязательно)</label>
                    <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('comment') }}</textarea>
                </div>
            </div>
            <div class="mt-6 text-right">
                <button type="submit" id="submit-button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Сохранить
                </button>
            </div>
        </form>
    </div>

    <!-- Список слитков -->
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold mb-4">Ваши активы</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата покупки</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Кол-во</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена покупки (за шт.)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Текущая цена (за шт.)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Разница (за шт.)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Общая разница</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($userBars as $bar)
                        @php
                            $priceDifference = $currentGoldPrice - $bar->purchase_price_per_bar;
                            $totalDifference = $priceDifference * $bar->quantity;
                            $colorClass = $priceDifference >= 0 ? 'text-green-600' : 'text-red-600';
                            $sign = $priceDifference >= 0 ? '+' : '';
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $bar->purchase_date->format('d.m.Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $bar->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($bar->purchase_price_per_bar, 2, ',', ' ') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ number_format($currentGoldPrice, 2, ',', ' ') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold {{ $colorClass }}">
                                {{ $sign }}{{ number_format($priceDifference, 2, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold {{ $colorClass }}">
                                {{ $sign }}{{ number_format($totalDifference, 2, ',', ' ') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">У вас пока нет добавленных слитков.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('gold-form').addEventListener('submit', function() {
        const button = document.getElementById('submit-button');
        button.disabled = true;
        button.innerText = 'Сохранение...';
    });
</script>
@endsection
