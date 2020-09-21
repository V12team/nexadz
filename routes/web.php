<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('app');
});

Route::get('/customers', 'CustomerController@index');
Route::get('/customers/dump', 'CustomerController@dump');

Route::group(['prefix' => 'customer'], function () {
    Route::get('/{id}', 'CustomerController@show');
    Route::group(['middleware' => \App\Http\Middleware\secureWebhook::class], function () {
        Route::get('/balance/update', 'CustomerController@updateBalance');
    });
});

Route::get('/adaccount/create/{id}', 'AdController@createAdAccount');
