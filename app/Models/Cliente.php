<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'direccion', 'localidad', 'contacto', 'documento', 'cuit_cuil', 'razon_social'
    ];

    protected $guarded = [
        'id'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function materiales_selections()
    {
        return $this->hasMany(MaterialesSelection::class);
    }
}
