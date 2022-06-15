<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateHeader extends Model
{
    use HasFactory;
    protected $table = 'template_header';
    protected $guarded = [];
}
