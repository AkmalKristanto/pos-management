<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Produk extends Model
{
   protected $connection = 'mysql';
   protected $table = 'produk';
   protected $primaryKey = 'id_produk';
   protected $fillable = [
      'id_toko',
      'id_outlet',
      'id_kategori',
      'nama_produk',
      'url_logo',
      'status_active',
   ];
   public $timestamps = false;
   protected $guarded = [];

   

}
