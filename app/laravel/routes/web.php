<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\FolderController;
use App\Http\Controllers\Public\PostController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/folder/{slug}', [FolderController::class, 'show'])->name('folder.show');
Route::get('/post/{slug}', [PostController::class, 'show'])->name('post.show');
