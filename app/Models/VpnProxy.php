<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class VpnProxy extends Model
{
    protected $fillable = [
        'ip',
        'port',
        'protocol',
        'country',
        'last_checked_at',
        'is_working',
    ];

}
