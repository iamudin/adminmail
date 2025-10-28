<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('admin');
});
