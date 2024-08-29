<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'type',
        'title',
        'url',
        'start_date',
        'end_date',
        'company_id',
        'is_allday'
    ];

}
