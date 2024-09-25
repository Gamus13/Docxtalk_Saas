<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\UrlPdfController;
use App\Http\Controllers\ObjetDocumentController;
use App\Http\Controllers\UserPdfController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/movies', [MovieController::class, 'index']);
Route::post('/create-movie', [MovieController::class, 'store']);

Route::post('/objet-document', [ObjetDocumentController::class, 'store']);

Route::post('/fileusers', [UserPdfController::class, 'upload']);

Route::post('/convert-url-to-pdf', [UrlPdfController::class, 'convert']);
