<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    protected $table = 'metode_pembayarans';

    protected $fillable = [
        'nama',
        'tipe',
        'nomor_rekening',
        'instruksi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'metode_pembayaran_id');
    }
}
