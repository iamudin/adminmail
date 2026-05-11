<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Desa extends Model
{
    use HasUuids;
function kecamatan(){
        return $this->belongsTo(Kecamatan::class);
}
function domain(){
        return $this->belongsTo(Domain::class);
}
}
