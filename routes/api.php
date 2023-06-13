<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'UserController@register');
Route::post('login', [ 'as' => 'login', 'uses' => 'UserController@login']);


Route::group(['middleware' => 'jwt.verify'], function () {
   
    Route::post('logout', 'UserController@logout');
    Route::post('save-token-fcm', 'UserController@generate_token_user');
    Route::get('me', 'UserController@me');
    
    Route::post('test-notifkasi', 'Pusat\PenawaranController@notify');

    /*Outlet = Produk*/
    Route::get('produk/list', 'ProdukController@list_produk');
    Route::get('produk/detail', 'ProdukController@detail_produk');
    Route::post('produk/create', 'ProdukController@create_produk');

    /*Outlet = Bahan*/
    Route::get('bahan/list', 'ProdukController@list_bahan');
    Route::get('bahan/detail', 'ProdukController@detail_bahan');
    Route::post('bahan/create', 'ProdukController@create_bahan');

    /*Outlet = Add-On*/
    Route::get('add-on/list', 'ProdukController@list_add_on');
    Route::get('add-on/detail', 'ProdukController@detail_add_on');
    Route::post('add-on/create', 'ProdukController@create_add_on');

    /*Admin - Outlet*/
    Route::get('outlet/list', 'OutletController@list_outlet');
    Route::get('outlet/detail', 'OutletController@detail_outlet');
    Route::post('outlet/create', 'OutletController@create_outlet');

});