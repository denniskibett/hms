<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/index', function () {
        return view('index');
    })->name('404');
    Route::get('/profile', function () {
        return view('profile');
    })->name('404');
    Route::get('/404', function () {
        return view('404');
    })->name('404');
    Route::get('/messages', function () {
        return view('messages');
    })->name('messages');
    Route::get('/alerts', function () {
        return view('alerts');
    })->name('alerts');
    Route::get('/blank', function () {
        return view('blank');
    })->name('blank');
    Route::get('/calendar', function () {
        return view('calendar');
    })->name('calendar');
    Route::get('/form-elements', function () {
        return view('form-elements');
    })->name('form-elements');
    Route::get('/basic-tables', function () {
        return view('basic-tables');
    })->name('basic-tables');
    Route::get('/avatars', function () {
        return view('avatars');
    })->name('avatars');
    Route::get('/badge', function () {
        return view('badge');
    })->name('badge');
    Route::get('/buttons', function () {
        return view('buttons');
    })->name('buttons');
    Route::get('/images', function () {
        return view('images');
    })->name('images');
    Route::get('/videos', function () {
        return view('videos');
    })->name('videos');
    Route::get('/signin', function () {
        return view('signin');
    })->name('signin');
    Route::get('/signup', function () {
        return view('signup');
    })->name('signup');
    Route::get('/image', function () {
        return view('image');
    });
    Route::get('/line-chart', function () {
        return view('line-chart');
    })->name('line-chart');
    Route::get('/bar-chart', function () {
        return view('bar-chart');
    })->name('bar-chart');
    Route::get('/dash', function () {
        return view('dash');
    })->name('dash');
});

require __DIR__.'/auth.php';
