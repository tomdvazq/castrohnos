<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NuevoPiedra extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre', 'direccion', 'localidad', 'contacto', 'documento', 'cuit_cuil', 'razon_social'
    ];

    protected $guarded = [
        'id'
    ];

    public function pedido_piedras()
    {
        return $this->hasMany(PedidoPiedra::class, 'cliente_id');
    }

    public function materiales_selections()
    {
        return $this->hasMany(MaterialesSelection::class);
    }
}
