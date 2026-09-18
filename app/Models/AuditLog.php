<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
    ];

    public function user(): belongsTo 
    {
        return $this->belongsTo(User::class);
    }
}
