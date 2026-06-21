<?php

namespace App\Listeners;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ProsesAktivasiSiswaOtomatis
{
    public function __construct()
    {
    }

    public function handle(Pembayaran $pembayaran): void
    {
        DB::transaction(function () use ($pembayaran) {
            
            $invoice = DB::table('invoice')->where('id', $pembayaran->invoice_id)->first();
            if (!$invoice) return;

            DB::table('invoice')
                ->where('id', $invoice->id)
                ->update([
                    'status_pembayaran' => 'Dibayar',
                    'updated_at' => now()
                ]);

            $pendaftaran = DB::table('pendaftaran')->where('id', $invoice->pendaftaran_id)->first();
            if (!$pendaftaran || $pendaftaran->status === 'Diterima') return;

            DB::table('pendaftaran')
                ->where('id', $pendaftaran->id)
                ->update([
                    'status' => 'Diterima',
                    'updated_at' => now()
                ]);

            $calonPeserta = DB::table('calon_peserta')->where('id', $pendaftaran->calon_peserta_id)->first();
            if (!$calonPeserta) return;

            $roleSiswa = DB::table('roles')->where('nama_role', 'Siswa')->first();
            $roleId = $roleSiswa ? $roleSiswa->id : Str::uuid(); // fallback jika role belum di-seed

            $userId = Str::uuid();
            DB::table('users')->insert([
                'id' => $userId,
                'name' => $calonPeserta->nama_lengkap,
                'nama_lengkap' => $calonPeserta->nama_lengkap,
                'email' => $calonPeserta->email,
                'no_hp' => $calonPeserta->no_hp,
                'password' => Hash::make('MulaiBelajar2026!'), 
                'role_id' => $roleId,
                'status_aktif' => true, 
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('siswa')->insert([
                'id' => Str::uuid(),
                'user_id' => $userId,
                'pendaftaran_id' => $pendaftaran->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        });
    }
}