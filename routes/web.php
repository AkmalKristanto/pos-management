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

Route::get('/barang', 'PenjualanController@index');
Route::get('/success-register', 'RegisterController@success');
Route::get('/api/check-role/{id}', 'RegisterController@checkRole');
Route::get('/register', 'RegisterController@index');
Route::post('/register/store', 'RegisterController@register');


Route::get('/register-sales', 'RegisterController@sales');
Route::post('/register-sales/store', 'RegisterController@store_sales');

Route::get('/', function () {
    return view('welcome');
});
