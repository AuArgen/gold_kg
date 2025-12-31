@extends('public.layout.base')

@section('title', 'Жеке кабинет')

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

                {{-- Негизги контент --}}
                <div class="flex-grow p-6 sm:p-10 bg-base-100 rounded-box shadow-xl mx-4 lg:ml-0 lg:mr-0">

                    {{-- Саламдашуу жана киришүү --}}
                    <div class="text-center mb-10">
                        <h1 class="text-3xl font-bold text-base-content mb-2">
                            Кош келиңиз, {{ Auth::user()->name ?? 'Колдонуучу' }}! 👋
                        </h1>
                        <p class="text-lg text-base-content/70">
                            Бул жерден сиз өзүңүздүн алтын активдериңизди башкарып, кирешеңизди көзөмөлдөй аласыз.
                        </p>
                    </div>

                    {{-- Тез аракеттер жана Статистика --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                        {{-- 1. Алтын кошуу картасы --}}
                        <div class="card bg-primary text-primary-content shadow-xl transform hover:scale-[1.02] transition-transform duration-300">
                            <div class="card-body items-center text-center">
                                <h2 class="card-title text-2xl mb-2">Жаңы алтын алдыңызбы?</h2>
                                <p class="mb-4">Портфелиңизге жаңы алтын куймасын кошуп, кирешеңизди эсептеңиз.</p>
                                <div class="card-actions justify-end">
                                    <a href="{{ route('my-gold.index') }}" class="btn btn-secondary btn-wide font-bold shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Алтын кошуу
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Статистика же маалымат картасы --}}
                        <div class="card bg-base-200 shadow-xl border border-base-300">
                            <div class="card-body">
                                <h2 class="card-title text-base-content">Менин активдерим</h2>
                                <p class="text-base-content/70 text-sm mb-4">Сиздин портфелдеги жалпы абал.</p>

                                <div class="stats stats-vertical lg:stats-horizontal shadow bg-base-100 w-full">
                                    <div class="stat place-items-center">
                                        <div class="stat-title">Куймалар</div>
                                        <div class="stat-value text-primary">
                                            {{-- Бул жерге контроллерден маалымат келсе жакшы болмок, азырынча статикалык же шилтеме --}}
                                            <a href="{{ route('my-gold.index') }}" class="link link-hover">Көрүү</a>
                                        </div>
                                        <div class="stat-desc">Толук тизме</div>
                                    </div>

                                    <div class="stat place-items-center">
                                        <div class="stat-title">Киреше</div>
                                        <div class="stat-value text-success">↗︎</div>
                                        <div class="stat-desc">Динамиканы көрүү</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Кошумча маалымат --}}
                    <div class="alert shadow-lg bg-base-200 border-l-4 border-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-info shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <h3 class="font-bold">Пайдалуу кеңеш!</h3>
                            <div class="text-xs">Алтындын баасы күн сайын өзгөрүп турат. "Менин слиткаларым" бөлүмүнөн учурдагы бааны текшерип туруңуз.</div>
                        </div>
                    </div>

                </div>
            </div>

            @include('client.component.left_navbar')
        </div>
    </div>
@endsection
