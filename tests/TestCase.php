<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // FAIL-SAFE: Memastikan database yang digunakan adalah 'testing'
        // Jika terdeteksi 'stockease', hentikan test seketika.
        if (DB::connection()->getDatabaseName() === 'stockease') {
            throw new \Exception('BAHAYA: Testing mencoba mengakses database utama (stockease)! Koneksi dihentikan untuk melindungi data Anda.');
        }

        // Memastikan konfigurasi selalu mengarah ke 'testing'
        Config::set('database.connections.mysql.database', 'testing');
    }
}
