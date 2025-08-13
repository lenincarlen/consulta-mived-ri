<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InmuebleController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/inmueble/getpropertiesbymatricula', [InmuebleController::class, 'getPropertiesByMatricula']);
Route::get('/inmueble/getPropertiesBySolarAndManzana', [InmuebleController::class, 'getPropertiesBySolarAndManzana']);
Route::get('/inmueble/getPropertiesByParcelaAndDC', [InmuebleController::class, 'getPropertiesByParcelaAndDC']);
