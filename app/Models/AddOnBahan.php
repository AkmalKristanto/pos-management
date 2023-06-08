<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class AddOnBahan extends Model
{
   protected $connection = 'mysql';
   protected $table = 'add_on_bahan';
   protected $primaryKey = 'id';
   protected $fillable = [
        'id_add_on',
        'id_bahan',
        'nama',
        'berat',
        'status_active'
   ];
   public $timestamps = false;
   protected $guarded = [];
}
