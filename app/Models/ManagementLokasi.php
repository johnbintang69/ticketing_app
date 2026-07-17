<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementLokasi extends Model
{
    protected $table = 'management_lokasis';

    protected $fillable = [
        'nama_lokasi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function events()
    {
        return $this->hasMany(Event::class, 'lokasi_id');
    }
}
