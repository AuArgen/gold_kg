@extends('app')

@section('title')
    @yield('title', 'Кыргызстандын алтын баалары')
@endsection

@section('main')
@include('public.layout.header')
<div class="flex-grow">
    @yield('content')
</div>
@include('public.layout.footer')
@endsection
