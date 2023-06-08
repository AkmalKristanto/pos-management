<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Toko extends Model
{
   protected $connection = 'mysql';
   protected $table = 'toko';
   protected $primaryKey = 'id_toko';
   protected $fillable = [
      'nama_toko',
      'alamat_toko',
      'id_subscription',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
