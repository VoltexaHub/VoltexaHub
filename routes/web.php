<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Forum/Index');
})->name('forum.index');

require __DIR__.'/auth.php';
