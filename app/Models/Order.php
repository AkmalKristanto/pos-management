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
        'nama_order',
        'jumlah',
        'tax',
        'service',
        'payment_method',
        'payment_status',
        'type_order',
        'total',
        'status_active',
        'is_draft',
   ];
   public $timestamps = false;
   protected $guarded = [];

   public function item() {
      return $this->hasMany(OrderProduk::class, 'id_order', 'id_order');
  }

}
