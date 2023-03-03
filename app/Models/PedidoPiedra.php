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

    public function piedras_selections()
    {
        return $this->hasMany(PiedrasSelection::class, 'pedido_id');
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'pedido_id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function materialesStock()
    {
        return $this->belongsTo(MaterialListado::class, 'material');
    }

    public function bachas()
    {
        return $this->hasMany(Bachas::class);
    }

    public function bachalistados()
    {
        return $this->hasMany(BachaListado::class);
    }
}
