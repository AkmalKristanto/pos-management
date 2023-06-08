<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class JurnalBahan extends Model
{
   protected $connection = 'mysql';
   protected $table = 'jurnal_bahan';
   protected $primaryKey = 'id_jurnal';
   protected $fillable = [
        'id_user',
        'id_toko',
        'id_bahan',
        'id_order',
        'jumlah',
        'balance',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
