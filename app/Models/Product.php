<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'description',
        'display_order',
        'is_active',
        'category_id',
        'sub_category_id',
        'image',
        'stock',
    ];

    protected $appends = ['image_url'];

    // Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // SubCategory
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**Accesors */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/product_images/' . $this->image);
        } else {
            return $this->images[0]->image_url??null;
        }
    }

    // images 
    public function images(){
        return $this->hasMany(Image::class);
    }
}
