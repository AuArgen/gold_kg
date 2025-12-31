@extends('public.layout.base')

@section('title', 'Менин алтын куймаларым')

@section('content')
<div class="max-w-7xl mx-auto my-2 py-8 sm:py-12 min-h-screen">
    <div class="drawer lg:drawer-open min-h-full">
        <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex flex-col">
            {{-- Мобилдик меню баскычы --}}
            <div class="lg:hidden px-4 mb-4">
                <label for="my-drawer-2" class="btn bg-base-100 drawer-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    Меню
                </label>
            </div>

            <div class="flex-grow p-4 sm:p-8 bg-base-100 rounded-box shadow-xl mx-4 lg:ml-0 lg:mr-0">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-base-content">Менин алтындарым</h1>
                </div>

                {{-- ФОРМА: Жаңы алтын кошуу --}}
                @if($goldBars->isNotEmpty())
                    <div class="collapse collapse-arrow bg-base-200 border border-base-300 rounded-box mb-8">
                        <input type="checkbox" />
                        <div class="collapse-title text-xl font-medium text-primary">
                            ➕ Жаңы алтын кошуу
                        </div>
                        <div class="collapse-content bg-base-100">
                            <form action="{{ route('my-gold.store') }}" method="POST" id="gold-form" class="pt-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- Куйманын түрү --}}
                                    <div class="form-control w-full">
                                        <label class="label"><span class="label-text font-medium">Куйманын салмагы</span></label>
                                        <select id="gold_bar_id" name="gold_bar_id" required class="select select-bordered w-full">
                                            @foreach($goldBars as $goldBar)
                                                <option value="{{ $goldBar->id }}">{{ $goldBar->name }} г</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Сатып алган күн --}}
                                    <div class="form-control w-full">
                                        <label class="label"><span class="label-text font-medium">Сатып алган күн</span></label>
                                        <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" required class="input input-bordered w-full">
                                    </div>

                                    {{-- Саны --}}
                                    <div class="form-control w-full">
                                        <label class="label"><span class="label-text font-medium">Саны (даана)</span></label>
                                        <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required class="input input-bordered w-full">
                                    </div>

                                    {{-- Комментарий --}}
                                    <div class="form-control w-full md:col-span-3">
                                        <label class="label"><span class="label-text font-medium">Эскертме (милдеттүү эмес)</span></label>
                                        <textarea id="comment" name="comment" rows="2" class="textarea textarea-bordered w-full" placeholder="Мисалы: Банктан алдым">{{ old('comment') }}</textarea>
                                    </div>
                                </div>

                                <div class="mt-6 text-right">
                                    <button type="submit" id="submit-button" class="btn btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Сактоо
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning shadow-lg mb-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <span>Системада алтын түрлөрү жок. Администраторго кайрылыңыз.</span>
                    </div>
                @endif

                {{-- ТАБЛИЦА: Активдер --}}
                <div class="bg-base-100 rounded-lg">
                    <h2 class="text-2xl font-bold mb-4 text-base-content">Сиздин активдер</h2>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            {{-- Таблицанын башы --}}
                            <thead class="bg-base-200 text-base-content">
                                <tr>
                                    <th>Куйма</th>
                                    <th>Сатып алган күн</th>
                                    <th>Саны</th>
                                    <th class="text-right">Сатып алуу баасы</th>
                                    <th class="text-right">Учурдагы баа</th>
                                    <th class="text-right">Айырма (1 даана)</th>
                                    <th class="text-right">Жалпы пайда</th>
                                    <th class="text-center">Аракеттер</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($userBars as $bar)
                                    @php
                                        $currentPriceRecord = $currentGoldPrices[$bar->gold_bar_id] ?? null;
                                        // Учурдагы баа (Сатып алуу баасы, анткени колдонуучу сатканда банк сатып алат)
                                        $currentPrice = $currentPriceRecord ? ($currentPriceRecord->buy_in_kopecks / 100) : 0;

                                        $priceDifference = $currentPrice - $bar->purchase_price_per_bar;
                                        $totalDifference = $priceDifference * $bar->quantity;

                                        $colorClass = $priceDifference >= 0 ? 'text-success' : 'text-error';
                                        $sign = $priceDifference >= 0 ? '+' : '';
                                    @endphp
                                    <tr class="hover">
                                        <td class="font-bold text-lg">{{ $bar->goldBar->name }} г</td>
                                        <td class="text-sm">{{ $bar->purchase_date->format('d.m.Y') }}</td>
                                        <td class="font-medium">{{ $bar->quantity }} шт</td>

                                        {{-- Сатып алуу баасы --}}
                                        <td class="text-right">
                                            <div class="font-medium">{{ number_format($bar->purchase_price_per_bar, 2, '.', ' ') }}</div>
                                            @if($bar->price_date)
                                                <div class="text-xs opacity-60">({{ $bar->price_date->format('d.m.Y') }})</div>
                                            @endif
                                        </td>

                                        {{-- Учурдагы баа --}}
                                        <td class="text-right font-bold">
                                            {{ number_format($currentPrice, 2, '.', ' ') }}
                                        </td>

                                        {{-- Айырма (1 даана) --}}
                                        <td class="text-right font-medium {{ $colorClass }}">
                                            {{ $sign }}{{ number_format($priceDifference, 2, '.', ' ') }}
                                        </td>

                                        {{-- Жалпы пайда (ОҢДОЛДУ: Текст кичирейтилди text-sm) --}}
                                        <td class="text-right">
                                            <span class="text-sm font-extrabold {{ $colorClass }}">
                                                {{ $sign }}{{ number_format($totalDifference, 2, '.', ' ') }} сом
                                            </span>
                                        </td>

                                        {{-- Аракеттер --}}
                                        <td class="text-center">
                                            <div class="join">
                                                <a href="{{ route('my-gold.edit', $bar->id) }}" class="btn btn-sm btn-ghost join-item text-info tooltip" data-tip="Өзгөртүү">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                </a>
                                                <form action="{{ route('my-gold.destroy', $bar->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Бул жазууну чын эле өчүргүңүз келеби?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-ghost join-item text-error tooltip" data-tip="Өчүрүү">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8">
                                            <div class="flex flex-col items-center justify-center text-base-content/50">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                                <p>Сизде азырынча кошулган алтын жок.</p>
                                                <p class="text-sm">Жогорудагы "Жаңы алтын кошуу" баскычын басыңыз.</p>
                                            </div>
                                        </td>
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
            button.innerHTML = '<span class="loading loading-spinner"></span> Сакталууда...';
        });
    }
</script>
@endsection
