<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
        'total_price',
        'status',
        'invoice_number',
        'snap_token',
	'recipient_name',
  	'phone',
  	'shipping_address',
  	'province',
  	'city',
  	'district',
  	'postal_code',

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
