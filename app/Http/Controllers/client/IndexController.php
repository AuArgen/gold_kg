<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request) {
        dd('Welcome to Client Page');
        return view('client.index');
    }
}
