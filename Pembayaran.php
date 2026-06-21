<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'pembayaran';

    protected $fillable = ['id', 'invoice_id', 'nominal', 'metode_pembayaran', 'provider', 'provider_reference', 'status', 'paid_at', 'callback_payload'];

    protected static function booted()
    {
        static::updated(function ($pembayaran) {
            
            if ($pembayaran->isDirty('status') && $pembayaran->status === 'Sukses') {
                
                app(\App\Listeners\ProsesAktivasiSiswaOtomatis::class)->handle($pembayaran);
                
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}