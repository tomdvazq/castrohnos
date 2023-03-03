<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiedrasSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id', 'material_id', 'material_listado_id', 'material', 'cantidad', 'entregado'
    ];

    protected $guarded = [
        'id'
    ];

    public function pedido_piedra()
    {
        return $this->belongsTo(PedidoPiedra::class, 'pedido_id');
    }
    

    public function materiales()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function materialesStock()
    {
        return $this->hasMany(MaterialListado::class);
    }
}
