<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialesSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        "pedido_id", "material_id", "material_listado_id", "cantidad", "material"
    ];

    protected $guarded = [
        'id'
    ];
    
    public function pedidos()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
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
