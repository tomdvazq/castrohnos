<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id', 'identificacion', 'categoria', 'tipo', 'archivo'
    ];

    protected $guarded = [
        'id'
    ];

    public function pedidos()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
