<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class ProdukVarian extends Model
{
   protected $connection = 'mysql';
   protected $table = 'produk_varian';
   protected $primaryKey = 'id_produk_varian';
   protected $fillable = [
        'id_produk',
        'nama_varian',
        'status_active',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
