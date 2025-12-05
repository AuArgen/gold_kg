@extends('public.layout.base')

@section('title', 'Редактировать запись')

@section('content')
<div class="max-w-7xl mx-auto my-2 py-8 sm:py-12 min-h-screen">
    <div class="drawer lg:drawer-open min-h-full">
        <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex flex-col">
            <div class="lg:hidden px-4 mb-4">
                <label for="my-drawer-2" class="btn bg-base-100 drawer-button">Меню</label>
            </div>

            <div class="flex-grow p-6 sm:p-10 bg-base-100 rounded-box shadow-xl mx-4 lg:ml-0 lg:mr-0">
                <h1 class="text-3xl font-bold mb-6 text-gray-800">Редактировать запись</h1>

                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <form action="{{ route('my-gold.update', $bar->id) }}" method="POST" id="gold-form">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="gold_bar_id" class="block text-sm font-medium text-gray-700">Вес слитка</label>
                                <select id="gold_bar_id" name="gold_bar_id" required class="mt-1 block w-full">
                                    @foreach($goldBars as $goldBar)
                                        <option value="{{ $goldBar->id }}" @if($bar->gold_bar_id == $goldBar->id) selected @endif>{{ $goldBar->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Дата покупки</label>
                                <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $bar->purchase_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Количество</label>
                                <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $bar->quantity) }}" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="md:col-span-3">
                                <label for="comment" class="block text-sm font-medium text-gray-700">Комментарий</label>
                                <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('comment', $bar->comment) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-between items-center">
                            <a href="{{ route('my-gold.index') }}" class="text-sm text-gray-600 hover:text-indigo-500">← Назад к списку</a>
                            <button type="submit" id="submit-button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Обновить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('client.component.left_navbar')
    </div>
</div>

<script>
    document.getElementById('gold-form').addEventListener('submit', function() {
        document.getElementById('submit-button').disabled = true;
        document.getElementById('submit-button').innerText = 'Обновление...';
    });
</script>
@endsection
