<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'gambar',
        'tanggal_waktu',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
    ];

    public function tikets()
    {
        return $this->hasMany(Tiket::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getStatusAttribute()
    {
        $now = now();
        if ($this->tanggal_waktu > $now) {
            return 'Upcoming';
        }

        if ($this->tanggal_waktu->copy()->addHours(3) >= $now) {
            return 'Ongoing';
        }

        return 'Completed';
    }

    public function hasSales(): bool
    {
        return $this->orders()->exists();
    }

    public function statusHistories()
    {
        return $this->hasMany(EventStatusHistory::class);
    }

    protected static function booted()
    {
        static::created(function ($event) {
            $event->statusHistories()->create([
                'old_status' => null,
                'new_status' => $event->status,
            ]);
        });

        static::updating(function ($event) {
            $originalTanggalWaktu = $event->getOriginal('tanggal_waktu');
            
            $oldStatus = 'Upcoming';
            if ($originalTanggalWaktu) {
                $originalTanggalWaktuObj = is_string($originalTanggalWaktu) 
                    ? \Carbon\Carbon::parse($originalTanggalWaktu) 
                    : $originalTanggalWaktu;

                $now = now();
                if ($originalTanggalWaktuObj > $now) {
                    $oldStatus = 'Upcoming';
                } elseif ($originalTanggalWaktuObj->copy()->addHours(3) >= $now) {
                    $oldStatus = 'Ongoing';
                } else {
                    $oldStatus = 'Completed';
                }
            }

            $newStatus = $event->status;

            if ($oldStatus !== $newStatus) {
                $event->statusHistories()->create([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
            }
        });
    }


    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_waktu', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('tanggal_waktu', '<=', now())
                     ->where('tanggal_waktu', '>=', now()->subHours(3));
    }

    public function scopeCompleted($query)
    {
        return $query->where('tanggal_waktu', '<', now()->subHours(3));
    }

    public function getImageUrlAttribute()
    {
        if ($this->gambar && filter_var($this->gambar, FILTER_VALIDATE_URL)) {
            return $this->gambar;
        }

        if (!empty($this->gambar) && file_exists(public_path('storage/' . $this->gambar))) {
            return asset('storage/' . $this->gambar);
        }

        return asset('storage/konser.jpg');
    }
}
