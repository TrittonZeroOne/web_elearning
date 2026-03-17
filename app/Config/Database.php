<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Config
 *
 * Koneksi PostgreSQL langsung ke Supabase.
 * Jika pdo_pgsql belum aktif, gunakan mode REST API saja
 * (AuthController tidak butuh koneksi DB).
 *
 * Untuk mengaktifkan koneksi DB:
 *   - Windows: extension=pdo_pgsql di php.ini
 *   - Linux:   sudo apt install php-pgsql
 *   - XAMPP:   uncomment extension=pdo_pgsql di php.ini
 */
class Database extends Config
{
    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'          => '',
        'hostname'     => '',        // isi di .env: database.default.hostname
        'username'     => 'postgres',
        'password'     => '',
        'database'     => 'postgres',
        'DBDriver'     => 'Postgre',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => false,     // false agar tidak crash jika koneksi gagal
        'charset'      => 'utf8',
        'DBCollat'     => 'utf8_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 5432,
        'numberNative' => false,
        'foundRows'    => false,
        'schema'       => 'public',
    ];
}
