<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'price',
        'discount_percent',
        'stock',
        'image'
    ];

    protected $appends = ['final_price', 'discount_amount'];

    public function getFinalPriceAttribute()
    {
        $price = (float) $this->price;
        $discount = min(100, max(0, (float) $this->discount_percent));
        return round($price * (1 - ($discount / 100)), 2);
    }

    public function getDiscountAmountAttribute()
    {
        return round((float) $this->price - $this->final_price, 2);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
}
