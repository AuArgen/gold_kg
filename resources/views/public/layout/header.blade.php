<header class="w-full bg-base-100/50 backdrop-blur sticky top-0 transition-shadow">
    <div class="w-7xl flex justify-between items-center mx-auto">
        <div class="py-2">
            <span class="font-bold text-xl"> {{ env('APP_NAME') }} </span>
        </div>

        <div class="flex gap-7 flex-col items-center md:flex-row">
            <a href="{{route('public.index')}}" class=""> Главная</a>
            <a href="/" class=""> Контакт</a>
            <a href="{{route('client.index')}}" class=""> Кабинет</a>
            @if(Auth::check())
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 rounded-full">
                            <img
                                alt="Tailwind CSS Navbar component"
                                src="{{auth()->user()->avatar}}" />
                        </div>
                    </div>
                    <ul
                        tabindex="0"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
{{--                        <li>--}}
{{--                            <a class="justify-between">--}}
{{--                                Profile--}}
{{--                                <span class="badge">New</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li><a>Settings</a></li>--}}
                        <li><a href="{{route('logout')}}">Выйти</a></li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</header>
