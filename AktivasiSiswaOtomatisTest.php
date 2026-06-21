<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AktivasiSiswaOtomatisTest extends TestCase
{
    use RefreshDatabase;

    public function test_alur_transaksi_pembayaran_sukses_otomatis_mengaktifkan_akun_siswa(): void
    {
        $roleId = Str::uuid();
        DB::table('roles')->insert([
            'id' => $roleId,
            'nama_role' => 'Siswa',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $calonPesertaId = Str::uuid();
        DB::table('calon_peserta')->insert([
            'id' => $calonPesertaId,
            'nama_lengkap' => 'Ahmad Fauzi',
            'email' => 'fauzi@roboticschool.com',
            'no_hp' => '085123456789',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $programId = Str::uuid();
        DB::table('program_kursus')->insert([
            'id' => $programId,
            'nama_program' => 'Robotics Arduino Dasar',
            'biaya' => 750000,
            'level' => 'Beginner',
            'durasi_minggu' => 8,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $pendaftaranId = Str::uuid();
        DB::table('pendaftaran')->insert([
            'id' => $pendaftaranId,
            'calon_peserta_id' => $calonPesertaId,
            'program_id' => $programId,
            'no_referensi' => 'REG-2026-0001',
            'tanggal_daftar' => now(),
            'status' => 'Menunggu Verifikasi',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $invoiceId = Str::uuid();
        DB::table('invoice')->insert([
            'id' => $invoiceId,
            'pendaftaran_id' => $pendaftaranId,
            'no_invoice' => 'INV-2026-0001',
            'total_tagihan' => 750000,
            'tanggal_terbit' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(2),
            'status_pembayaran' => 'Menunggu',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $pembayaran = Pembayaran::create([
            'id' => Str::uuid(),
            'invoice_id' => $invoiceId,
            'nominal' => 750000,
            'metode_pembayaran' => 'Transfer Bank',
            'status' => 'Pending',
        ]);

        $pembayaran->update([
            'status' => 'Sukses',
            'paid_at' => now()
        ]);

        $this->assertDatabaseHas('invoice', [
            'id' => $invoiceId,
            'status_pembayaran' => 'Dibayar'
        ]);

        $this->assertDatabaseHas('pendaftaran', [
            'id' => $pendaftaranId,
            'status' => 'Diterima'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'fauzi@roboticschool.com',
            'status_aktif' => true,
            'role_id' => $roleId
        ]);

        $this->assertDatabaseHas('siswa', [
            'pendaftaran_id' => $pendaftaranId
        ]);
    }
}