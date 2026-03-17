<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use App\Filters\AuthFilter;
use App\Filters\RoleFilter;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'auth'     => AuthFilter::class,
        'role'     => RoleFilter::class,
    ];
    public array $globals = [
        'before' => [],
        'after'  => ['toolbar'],
    ];
    public array $methods = [];
    public array $filters = [];
}
