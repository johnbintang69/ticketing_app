<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_harga',
        'event_id',
        'order_date',
        'metode_pembayaran_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tikets()
    {
        return $this->belongsToMany(Tiket::class, 'detail_orders')->withPivot('jumlah', 'subtotal_harga');
    }

    public function events()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function detailOrders()
    {
        return $this->hasMany(DetailOrder::class);
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }
}