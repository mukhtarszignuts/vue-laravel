<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'owner_id',
        'address',
        'employee_size',
        'logo',
        'is_active',
    ];

    protected $appends = ['image_url'];

    //owner
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getImageUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/company/logo/' . $this->logo);
        } else {
            return null;
        }
    }

    // Employee 
    public function employees(){
        return $this->hasMany(User::class,'company_id');
    }
}
