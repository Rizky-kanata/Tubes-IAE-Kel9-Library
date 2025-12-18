<?php

use Illuminate\Support\Facades\Route;
use App\Models\Book;
use App\Models\Category;

Route::get('/', function () {
    return view('welcome');
});


