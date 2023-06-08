<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Outlet extends Model
{
   protected $connection = 'mysql';
   protected $table = 'outlet';
   protected $primaryKey = 'id_outlet';
   protected $fillable = [
        'id_user',
        'id_toko',
        'nama_outlet',
        'alamat_outlet',
        'status_active',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
