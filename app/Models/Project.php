<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'description',
        'location',
        'type',
        'start_date',
        'end_date',
        'total_budget',
        'budget',
        'client_id',
        'company_id',
        'status',
        'thumbnil',
    ];

    // client
    public function client(){
        return $this->belongsTo(User::class,'client_id');
    }


    // images
    public function images(){
        return $this->hasMany(Image::class);
    }

}
