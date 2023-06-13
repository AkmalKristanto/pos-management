<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

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
}
