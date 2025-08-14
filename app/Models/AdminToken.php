<?php

// app/Models/AdminToken.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminToken extends Model
{
    protected $fillable = ['admin_id', 'token_hash', 'expires_at', 'ip', 'user_agent'];
    protected $dates = ['expires_at'];
}
