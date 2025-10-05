@extends('app')

@section('title')
    @yield('title', 'Цены золота кыргызстана')
@endsection

@section('main')
@include('public.layout.header')
<div class="flex-grow">
    @yield('content')
</div>
@include('public.layout.footer')
@endsection
