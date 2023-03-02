<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoPiedra extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'identificacion', 'estado', 'entrega', 'seña'
    ];

    protected $guarded = [
        'id'
    ];

    protected $dates = [
        'entrega'
    ];

    public function clientes()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function materialesStock()
    {
        return $this->belongsTo(MaterialListado::class, 'material');
    }
}
