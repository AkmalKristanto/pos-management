<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class OrderProduk extends Model
{
   protected $connection = 'mysql';
   protected $table = 'order_produk';
   protected $primaryKey = 'id_order_produk';
   protected $fillable = [
        'id_produk',
        'id_produk_add_on',
        'id_produk_varian',
        'qty',
        'keterangan',
        'harga',
        'harga_total',
        'status_active',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
