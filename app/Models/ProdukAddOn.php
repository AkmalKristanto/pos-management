<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class ProdukAddOn extends Model
{
   protected $connection = 'mysql';
   protected $table = 'produk_add_on';
   protected $primaryKey = 'id_produk_add_on';
   protected $fillable = [
        'id_produk',
        'id_add_on',
        'nama',
        'status_active',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
