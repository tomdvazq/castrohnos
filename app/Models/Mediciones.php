<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mediciones extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'identificacion', 'cliente_id', 'estado', 'entrega', 'remedir', 'avisa', 'medido'
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

    // Relación con base de datos de materiales

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
        return $this->hasMany(MaterialesSelection::class, 'pedido_id');
    }

    // Relación con base de datos de bachas

    public function bachas()
    {
        return $this->hasMany(Bachas::class);
    }

    public function bachasStock()
    {
        return $this->hasMany(BachaListado::class, 'id');
    }

    public function bachas_selections()
    {
        return $this->hasMany(BachasSelection::class, 'pedido_id');
    }

    // Relacion con base de datos de bachas

    public function accesorios()
    {
        return $this->hasMany(Accesorios::class);
    }

    public function accesorios_selections()
    {
        return $this->hasMany(AccesoriosSelection::class, 'pedido_id');
    }

    public function accesoriosStock()
    {
        return $this->belongsTo(AccesorioListado::class, 'tipo');
    }
}
