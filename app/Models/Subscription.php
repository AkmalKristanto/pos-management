<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Subscription extends Model
{
   protected $connection = 'mysql';
   protected $table = 'subscription';
   protected $primaryKey = 'id_subscription';
   protected $fillable = [
        'nama',
        'deskripsi',
        'url_logo',
        'harga',
        'status_active',
   ];
   public $timestamps = false;
   protected $guarded = [];
}
