<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'report_link'
    ];

    protected $attributes = [
        'report_link' => ''
    ];

    protected $hidden = [
        'updated_at'
    ];
}
