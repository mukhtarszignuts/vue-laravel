<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory;

    protected $table = 'images';

    protected $fillable = [
        'type',
        'product_id',
        'project_id',
        'image',
    ];

    protected $appends = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/product_images/' . $this->image);
        } else {
            return null;
        }
    }
}
