<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Order extends Model
{
   protected $connection = 'mysql';
   protected $table = 'order';
   protected $primaryKey = 'id_order';
   protected $fillable = [
        'id_user',
        'id_toko',
        'id_outlet',
        'no_order',
        'jumlah',
        'tax',
        'service',
        'total',
        'status_active',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
