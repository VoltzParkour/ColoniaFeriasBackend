<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/session', 'Controller@Session');
Route::post('/payment/boleto', 'Controller@BoletoPayment');
Route::post('/payment/card', 'Controller@CardPayment');
Route::post('/pagseguro/notification', 'Controller@Confirmation');
Route::post('/pagseguro/confirm', 'Controller@Confirm');
Route::post('/pagseguro/check', 'Controller@Check');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
