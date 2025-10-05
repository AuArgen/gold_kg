@extends('public.layout.base')

@section('title', env('APP_NAME').' авторизация')

@section('content')
    <div class="w-7xl mx-auto bg-base-300 flex justify-center my-12">
        <div class="w-96">
            <div class="p-4 text-center">
                <span class="text-2xl font-bold ">{{env('APP_NAME')}}</span>
            </div>

        </div>
    </div>
@endsection
