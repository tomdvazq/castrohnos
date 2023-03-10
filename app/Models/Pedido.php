<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'identificacion', 'cliente_id', 'estado', 'entrega', 'remedir', 'avisa', 'medido', 'confirmacion', 'seña', 'total'
    ];

    protected $guarded = [
        'id'
    ];

    protected $dates = [
        'entrega', 'remedir', 'avisa', 'medido'
    ];

    public function clientes()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function nuevos()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'pedido_id');
    }

    // Relacion con base de datos de materiales

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function materialesStock()
    {
        return $this->belongsTo(MaterialListado::class, 'material');
    }

    public function materiales_selections()
    {
        return $this->hasMany(MaterialesSelection::class);
    }

    // Relacion con base de datos de bachas

    public function bachas()
    {
        return $this->hasMany(Bachas::class);
    }

    public function bachasStock()
    {
        return $this->belongsTo(BachaListado::class, 'material');
    }

    public function bachas_selections()
    {
        return $this->hasMany(BachasSelection::class);
    }
}
