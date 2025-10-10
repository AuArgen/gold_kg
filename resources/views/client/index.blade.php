@extends('public.layout.base')

@section('title', 'Личный кабинет')

@section('content')
    {{--
        Общий контейнер для центрирования макета
        Убран общий фон (bg-base-200).
    --}}
    <div class="max-w-7xl mx-auto my-2 py-8 sm:py-12 min-h-screen">

        {{--
            Основной Drawer.
            p-0: Убран общий padding с drawer-content, чтобы контентная область сама контролировала padding.
        --}}
        <div class="drawer lg:drawer-open min-h-full">

            {{-- 1. Скрытый чекбокс для управления состоянием drawer --}}
            <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

            {{-- 2. Основная контентная область (Правая часть на десктопе) --}}
            <div class="drawer-content flex flex-col">

                {{-- Кнопка для открытия меню (только на маленьких экранах) --}}
                <div class="lg:hidden px-4 mb-4">
                    <label for="my-drawer-2" class="btn bg-base-100 drawer-button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        Меню
                    </label>
                </div>

                {{-- Контент --}}
                <div class="flex-grow p-6 sm:p-10 bg-base-100 rounded-box shadow-xl mx-4 lg:ml-0 lg:mr-0">
                    <h1 class="text-3xl font-bold mb-4 text-center text-base-content">
                        Добро пожаловать в личный кабинет!
                    </h1>
                    <p class="text-lg text-center text-base-content/80">
                        Это ваша персональная страница. Здесь вы можете управлять своими данными, просматривать историю заказов и настраивать профиль.
                    </p>

                    {{-- Дополнительный контент --}}
                    <div class="mt-8">
                        <div class="alert alert-info shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>У вас 4 новых сообщения! Проверьте вкладку "Сообщения".</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Sidebar (Левая часть) --}}
            {{-- Отступы p-0: удален лишний padding вокруг drawer-side --}}
            <div class="drawer-side p-0">
                {{-- drawer-overlay: Элемент, который получает автоматический z-index при открытии --}}
                <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>

                {{-- Само меню. w-64 - ширина. rounded-box: добавлен класс, чтобы углы были скруглены --}}
                <div class="w-64 p-4 bg-base-100 rounded-box shadow-xl flex flex-col h-full m-4 lg:m-0">
                    <h2 class="text-xl font-bold mb-4 text-base-content">Меню</h2>
                    <ul class="menu p-0 text-base-content flex-grow space-y-2">
                        {{-- Стилизованные кнопки для меню --}}
                        <li><a class="btn btn-sm btn-block justify-start  bg-base-200">🏠 Главная</a></li>
                        <li><a class="btn btn-sm btn-block justify-start btn-ghost">⚙️ Настройки</a></li>
                        <li><a class="btn btn-sm btn-block justify-start btn-ghost">👤 Профиль</a></li>
                        <li>
                            <a class="btn btn-sm btn-block justify-start btn-ghost">
                                📧 Сообщения
                                <div class="badge badge-secondary ml-auto">4</div>
                            </a>
                        </li>
                        <li><a class="btn btn-sm btn-block justify-start btn-ghost">📝 Мои заказы</a></li>
                    </ul>

{{--                    <div class="mt-auto pt-4 border-t border-base-200">--}}
{{--                        <a class="btn btn-error btn-block justify-start">🚪 Выход</a>--}}
{{--                    </div>--}}
                </div>
            </div>
        </div>
    </div>
@endsection
