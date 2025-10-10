@extends('public.layout.base')

@section('title', 'Главная страница')

@section('content')
    <div class="md:w-7xl mx-auto">
        <div class="flex flex-col">
            <h1>
                <center>
                    Welcome to {{env('APP_NAME')}}!
                </center>
            </h1>
        </div>
    </div>
@endsection
