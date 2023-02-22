<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'identificacion', 'cliente_id', 'estado', 'entrega'
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

    public function nuevos()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function materiales_selections()
    {
        return $this->hasMany(MaterialesSelection::class);
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