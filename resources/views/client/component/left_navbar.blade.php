<div class="drawer-side p-0">
    <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>

    {{-- Само меню. w-64 - ширина. rounded-box: добавлен класс, чтобы углы были скруглены --}}
    <div class="w-64 p-4 bg-base-100 rounded-box shadow-xl flex flex-col h-full m-4 lg:m-0">
        <h2 class="text-xl font-bold mb-4 text-base-content">Меню</h2>
        <ul class="menu p-0 text-base-content flex-grow space-y-2">

            {{-- Стилизованные кнопки для меню --}}
            <li><a class="btn btn-sm btn-block justify-start bg-base-200">🏠 Главная</a></li>

            {{-- Блок для роли ID = 1 (Золото) --}}
            @if(Auth::check() && Auth::user()->roles()->where('role_id', 1)->exists())
                <li>
                    <a href="{{route('admin.gold')}}" class="btn btn-sm btn-block justify-start btn-warning text-warning-content">
                        👑 Золото (Premium)
                    </a>
                </li>
                <li>
                    <a href="{{route('admin.logs')}}" class="btn btn-sm btn-block justify-start btn-warning text-warning-content">
                        Логи (Premium)
                    </a>
                </li>
                <li>
                    <a href="{{route('admin.contact')}}" class="btn btn-sm btn-block justify-start btn-warning text-warning-content">
                        Контакт (Premium)
                    </a>
                </li>
            @endif

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

        {{-- СООБЩЕНИЕ О РАЗРАБОТКЕ (ЕСЛИ НЕТ НИ ОДНОЙ РОЛИ) --}}
        @if(Auth::check() && !Auth::user()->roles()->exists())
            <div class="alert alert-info mt-4 p-3 shadow-md">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="flex flex-col">
                        <span class="font-bold">Ролевые функции</span>
                        <span class="text-xs">Идет разработка. Скоро будут доступны!</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
