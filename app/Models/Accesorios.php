<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesorios extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca'
    ];

    protected $guarded = [
        'id'
    ];

    public function accesoriosStock()
    {
        return $this->hasMany(AccesorioListado::class, 'accesorio_id');
    }

    public function accesorios_selection()
    {
        return $this->hasMany(AccesoriosSelection::class);
    }
}
