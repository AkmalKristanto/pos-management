<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class AddOn extends Model
{
   protected $connection = 'mysql';
   protected $table = 'add_on';
   protected $primaryKey = 'id_add_on';
   protected $fillable = [
        'id_toko',
        'nama',
        'status_active'
   ];
   public $timestamps = false;
   protected $guarded = [];
}
