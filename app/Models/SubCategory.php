<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'name',
        'display_order',
        'is_active',
        'category_id',
    ];

    // Category
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

    // product 
    public function products(){
        return $this->hasMany(Product::class,'sub_category_id');
    }
}
