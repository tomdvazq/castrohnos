<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccesorioListado extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo', 'modelo', 'stock'
    ];

    protected $guarded = [
        'id', 'accesorio_id'
    ];

    public function accesorios()
    {
        return $this->belongsToMany(Accesorios::class);
    }

    public function accesorios_selections()
    {
        return $this->belongsTo(AccesoriosSelection::class);
    }
}
