<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="ut3BRGymSrPXPHreS27VuINWoKjVvyPz5Mxyu36FX-w" />

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=104609211', 'ym');

        ym(104609211, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/104609211" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->



    {{-- SEO: Основные мета-теги --}}
    <title>@yield('title', 'Gold KG | Актуальные цены на золото и инвестиционные калькуляторы')</title>

    <meta name="description" content="@yield('description', 'Актуальные цены на мерные слитки золота в сомах. Удобный калькулятор для оценки стоимости и получения советов по выгодному инвестированию в драгоценные металлы.')">
    <meta name="keywords" content="золото, цены на золото, мерные слитки, инвестиции в золото, калькулятор золота, сом, KG">

    {{-- SEO: Канонический URL (важно для избежания дублирования) --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- SEO: Open Graph (Для социальных сетей) --}}
    <meta property="og:title" content="@yield('title', 'Gold KG | Актуальные цены на золото')">
    <meta property="og:description" content="@yield('description', 'Актуальные цены на мерные слитки золота и калькуляторы для инвестиций.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    {{-- Если у вас есть логотип или изображение для превью, добавьте: --}}
    {{-- <meta property="og:image" content="{{ asset('images/logo-social.png') }}"> --}}


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif

    @stack('styles')

</head>
<body class="bg-base-100">
<div class="min-h-screen flex flex-col">
    @yield('main')
</div>
@stack('scripts')
</body>
</html>
