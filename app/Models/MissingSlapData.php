<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissingSlapData extends Model
{
    use HasFactory;
    protected $table = 'missing_slap_data';
    protected $guarded =[];
}
