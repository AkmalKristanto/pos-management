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

/*General*/
Route::group(['middleware' => 'jwt.verify'], function () {
   
    Route::post('logout', 'UserController@logout');
    Route::post('save-token-fcm', 'UserController@generate_token_user');
    Route::get('me', 'UserController@me');
    Route::post('profile/upload-logo-profile', 'UserController@upload_image_profile');
    
    Route::post('test-notifkasi', 'Pusat\PenawaranController@notify');
});

/*SuperAdmin*/
Route::group(['middleware' => 'jwt.verify'], function () {

    /*Manage Toko*/
    Route::get('admin/toko/list', 'SuperAdmin\ManageTokoController@list_toko');
    Route::get('admin/toko/detail', 'SuperAdmin\ManageTokoController@detail_toko');
    Route::post('admin/toko/create', 'SuperAdmin\ManageTokoController@create_toko');

    /*Manage Toko*/
    Route::get('admin/outlet/list', 'SuperAdmin\ManageTokoController@list_outlet');
    Route::get('admin/outlet/detail', 'SuperAdmin\ManageTokoController@detail_outlet');
    Route::post('admin/outlet/create', 'SuperAdmin\ManageTokoController@create_outlet');

});

/*Outlet*/
Route::group(['middleware' => 'jwt.verify'], function () {

    /*Produk*/
    Route::get('produk/list', 'ProdukController@list_produk');
    Route::get('produk/detail', 'ProdukController@detail_produk');
    Route::post('produk/update', 'ProdukController@update_produk');
    Route::post('produk/create', 'ProdukController@create_produk');
    Route::post('produk/delete', 'ProdukController@delete_produk');


    /*Bahan*/
    Route::get('bahan/list', 'ProdukController@list_bahan');
    Route::get('bahan/detail', 'ProdukController@detail_bahan');
    Route::post('bahan/create', 'ProdukController@create_bahan');

    /*Add-On*/
    Route::get('add-on/list', 'ProdukController@list_add_on');
    Route::get('add-on/detail', 'ProdukController@detail_add_on');
    Route::post('add-on/create', 'ProdukController@create_add_on');

    /*Menu*/
    Route::get('menu/list', 'ProdukController@list_produk');
    Route::get('menu/detail', 'ProdukController@detail_menu');
    Route::post('menu/create', 'ProdukController@create_produk');

    /*Transaksi*/
    Route::get('transaksi/list', 'TransaksiController@list_transaksi');
    Route::get('transaksi/list-draft', 'TransaksiController@list_draft_transaksi');
    Route::post('transaksi/draft', 'TransaksiController@draft_transaksi');
    Route::get('transaksi/detail', 'TransaksiController@detail_transaksi');
    Route::post('transaksi/create', 'TransaksiController@create_transaksi');
});

/*Toko*/
Route::group(['middleware' => 'jwt.verify'], function () {

    /*Outlet*/
    Route::get('outlet/list', 'OutletController@list_outlet');
    Route::get('outlet/detail', 'OutletController@detail_outlet');
    Route::post('outlet/create', 'OutletController@create_outlet');
});