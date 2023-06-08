<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Bahan extends Model
{
   protected $connection = 'mysql';
   protected $table = 'bahan';
   protected $primaryKey = 'id_bahan';
   protected $fillable = [
        'id_outlet',
        'id_toko',
        'nama_bahan',
        'berat_awal',
        'berat_akhir',
        'qty',
        'harga_total',
        'status_active',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
