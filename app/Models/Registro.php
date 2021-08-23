<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $connection='bank';
    protected $table='users';
    protected $primaryKey = "id";
    //public $timestamps=false;
}
