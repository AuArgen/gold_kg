@extends('public.layout.base')

@section('title', 'Байланыш | ' . env('APP_NAME'))
@section('description', 'Биз менен байланышып, алтын баалары жана инвестиция боюнча суроолоруңузга жооп алыңыз.')

@section('content')
    <div class="max-w-7xl mx-auto my-12 px-4 sm:px-6 lg:px-8">

        <header class="text-center py-10 mb-8 bg-base-100 rounded-2xl shadow-xl">
            <h1 class="text-4xl md:text-5xl font-extrabold text-primary mb-3">
                Биз менен байланышуу
            </h1>
            <p class="text-lg text-base-content/80 max-w-2xl mx-auto">
                Алтын баалары, сатып алуу шарттары же инвестициялык мүмкүнчүлүктөр боюнча суроолоруңуз болсо, биз жооп берүүгө даярбыз.
            </p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

            {{-- 1. Байланыш формасы --}}
            <div class="card bg-base-100 shadow-2xl border border-base-300">
                <form class="card-body" method="POST" action="{{route('public.submit_contact')}}">
                    @csrf
                    <h2 class="card-title text-2xl font-bold text-secondary mb-4">Билдирүү жөнөтүү</h2>

                    {{-- Аты-жөнү --}}
                    <label class="form-control w-full">
                        <div class="label"><span class="label-text font-medium">Сиздин атыңыз</span></div>
                        <input type="text" name="name" placeholder="Асан Үсөнов" class="input input-bordered w-full bg-base-200 @error('name') input-error @enderror" value="{{ old('name') }}" required />
                        @error('name') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </label>

                    {{-- Email --}}
                    <label class="form-control w-full mt-2">
                        <div class="label"><span class="label-text font-medium">Сиздин Email</span></div>
                        <input type="email" name="email" placeholder="example@mail.com" class="input input-bordered w-full bg-base-200 @error('email') input-error @enderror" value="{{ old('email') }}" required />
                        @error('email') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </label>

                    {{-- Билдирүү --}}
                    <label class="form-control w-full mt-2">
                        <div class="label"><span class="label-text font-medium">Билдирүүңүз</span></div>
                        <textarea name="message" class="textarea textarea-bordered h-24 w-full bg-base-200 @error('message') textarea-error @enderror" placeholder="Сурооңуз же сунушуңуз..." required>{{ old('message') }}</textarea>
                        @error('message') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </label>

                    <div class="card-actions justify-end mt-6">
                        <button type="submit" class="btn btn-primary btn-block shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.902l-6.837 4.148a2.25 2.25 0 01-2.193 0l-6.837-4.148A2.25 2.25 0 012.25 6.993V6.75" />
                            </svg>
                            Жөнөтүү
                        </button>
                    </div>

                    {{-- Ийгиликтүү билдирүү --}}
                    @if(session('success'))
                        <div role="alert" class="alert alert-success shadow-lg mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Ката билдирүүсү --}}
                    @if(session('error'))
                        <div role="alert" class="alert alert-error shadow-lg mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                </form>
            </div>

            {{-- 2. Байланыш маалыматтары --}}
            <div>
                <h2 class="text-3xl font-bold text-base-content mb-6">Биздин даректер</h2>

                <div class="space-y-6">
                    {{-- Телефон --}}
                    <div class="flex items-center space-x-4 p-4 bg-base-200 rounded-lg shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 00.948.684l1.498 4.493a1 1 0 00-.502 1.21l-2.257 2.257a11.096 11.096 0 006.102 6.102l2.257-2.257a1 1 0 001.21-.502l4.493 1.498a1 1 0 00.684.948V19a2 2 0 01-2 2h-4.594A18.845 18.845 0 013 5.594V5z" />
                        </svg>
                        <div>
                            <p class="text-base-content/70 text-sm">Телефон</p>
                            <a href="tel:+996703000000" class="text-lg font-semibold text-base-content hover:text-primary transition duration-200">+996 703 000 000</a>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="flex items-center space-x-4 p-4 bg-base-200 rounded-lg shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <p class="text-base-content/70 text-sm">Суроолор үчүн Email</p>
                            <a href="mailto:info@example.kg" class="text-lg font-semibold text-base-content hover:text-primary transition duration-200">info@example.kg</a>
                        </div>
                    </div>

                    {{-- Дарек --}}
                    <div class="flex items-center space-x-4 p-4 bg-base-200 rounded-lg shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <div>
                            <p class="text-base-content/70 text-sm">Биздин кеңсе</p>
                            <p class="text-lg font-semibold text-base-content">Бишкек ш., Ибраимов көч., 103</p>
                        </div>
                    </div>
                </div>

                {{-- Карта --}}
                <div class="mt-8 rounded-lg overflow-hidden shadow-xl border border-base-300">
                    <h3 class="text-center p-3 bg-base-200 font-medium text-base-content">Картадан көрүү</h3>
                    <div class="w-full h-64 bg-base-300 flex items-center justify-center text-base-content/70">
                        {{-- Картанын кодун бул жерге коюңуз --}}
                        <p>Интерактивдүү карта жүктөлүүдө...</p>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
