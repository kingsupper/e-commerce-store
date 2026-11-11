<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = ['user_id', 'address_id', 'total', 'status'];
    // الطلب بينتمي ليوزر واحد
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // الطلب فيه عنوان
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    // الطلب فيه عناصر كتير (منتجات)
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
