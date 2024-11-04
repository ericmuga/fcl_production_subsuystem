<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offals extends Model
{
    protected $fillable = ['product_code', 'scale_reading', 'net_weight', 'is_manual', 'user_id'];
}
