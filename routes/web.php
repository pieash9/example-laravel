<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/books', [BookController::class, 'index'])->name('books.index');

    Route::middleware('role:member')->group(function () {
        Route::get('/borrows', [BorrowController::class, 'index'])->name('borrows.index');
        Route::get('/borrows/create', [BorrowController::class, 'create'])->name('borrows.create');
        Route::post('/borrows', [BorrowController::class, 'store'])->name('borrows.store');
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('books', BookController::class)->except(['index', 'show']);

        Route::get('/manage/borrows', [BorrowController::class, 'index'])->name('borrows.manage');
        Route::post('/manage/borrows/{borrow}/approve', [BorrowController::class, 'approve'])->name('borrows.approve');
        Route::post('/manage/borrows/{borrow}/reject', [BorrowController::class, 'reject'])->name('borrows.reject');
        Route::post('/manage/borrows/{borrow}/return', [BorrowController::class, 'markReturned'])->name('borrows.return');
    });
});
