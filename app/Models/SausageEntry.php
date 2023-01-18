<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SausageEntry extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'sausage_entries';
}
