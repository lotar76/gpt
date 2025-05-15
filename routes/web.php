<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $res = \App\Models\VpnProxy::all();
    return $res;
//    return view('welcome');
});


