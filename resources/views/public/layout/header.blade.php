<header class="w-full bg-base-100 shadow-md sticky top-0 z-50 transition-shadow">
    <div class="navbar max-w-7xl mx-auto">

        {{-- 1. Логотип / Бренд (Ар дайым сол жакта) --}}
        <div class="navbar-start">
            <a href="{{ route('public.index') }}" class="btn btn-ghost text-xl">
                <span class="font-bold text-base-content"> {{ env('APP_NAME') }} </span>
            </a>
        </div>

        {{-- 2. Негизги меню (Мобилдикте жашырылган) --}}
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 gap-2">
                {{-- Башкы бет --}}
                <li>
                    <a href="{{ route('public.index') }}"
                       class="btn btn-ghost btn-sm text-base-content {{ request()->routeIs('public.index') ? 'btn-active bg-base-200' : '' }}">
                        Башкы бет
                    </a>
                </li>
                {{-- Байланыш --}}
                <li>
                    <a href="{{ route('public.contact') }}"
                       class="btn btn-ghost btn-sm text-base-content {{ request()->routeIs('public.contact') ? 'btn-active bg-base-200' : '' }}">
                        Байланыш
                    </a>
                </li>
                {{-- Кабинет --}}
                <li>
                    <a href="{{ route('client.index') }}"
                       class="btn btn-sm text-base-content {{ request()->routeIs('client.index') ? 'btn-active bg-base-200' : '' }}">
                        Кабинет
                    </a>
                </li>
            </ul>
        </div>

        {{-- 3. Колдонуучу элементтери / Кирүү баскычы (Ар дайым оң жакта) --}}
        <div class="navbar-end">
            @if(Auth::check())
                {{-- Профиль менюсу (Аватар) --}}
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 rounded-full">
                            <img alt="User Avatar" src="{{ auth()->user()->avatar ?? 'https://i.pravatar.cc/150?img=68' }}" />
                        </div>
                    </div>
                    <ul
                        tabindex="0"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow-lg border border-base-200">
                        <li>
                            <a href="{{ route('client.index') }}" class="{{ request()->routeIs('client.index') ? 'active' : '' }}">
                                Жеке кабинет
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('my-gold.index') }}" class="{{ request()->routeIs('my-gold.index') ? 'active' : '' }}">
                                Менин алтындарым
                            </a>
                        </li>
                        <div class="divider my-0"></div>
                        <li><a href="{{ route('logout') }}" class="text-error hover:bg-error hover:text-white">Чыгуу</a></li>
                    </ul>
                </div>
            @else
                {{-- "Кирүү" баскычы коноктор үчүн --}}
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline btn-primary hidden md:flex">Кирүү</a>
            @endif

            {{-- Мобилдик версия үчүн бургер-меню --}}
            <div class="dropdown dropdown-end lg:hidden ml-2">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow-lg border border-base-200">
                    <li><a href="{{ route('public.index') }}" class="{{ request()->routeIs('public.index') ? 'active font-semibold' : '' }}">Башкы бет</a></li>
                    <li><a href="{{ route('public.contact') }}" class="{{ request()->routeIs('public.contact') ? 'active font-semibold' : '' }}">Байланыш</a></li>
                    <li><a href="{{ route('client.index') }}" class="{{ request()->routeIs('client.index') ? 'active font-semibold' : '' }}">Кабинет</a></li>
                    @if(!Auth::check())
                        <div class="divider my-0"></div>
                        <li><a href="{{ route('login') }}">Кирүү</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</header>
