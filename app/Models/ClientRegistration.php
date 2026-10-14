<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientRegistration extends Model
{
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class, 'subdomain', 'subdomain');
    }
}
