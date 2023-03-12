<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccesoriosSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id', 'accesorio_id', 'accesorio_listado_id','cantidad', 'material'
    ];

    protected $guarded = [
        'id', 'pedido_id', 'accesorio_id', 'accesorio_listado_id'
    ];
    
    public function pedidos()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
    

    public function accesorios()
    {
        return $this->belongsTo(Accesorios::class);
    }

    public function accesoriosStock()
    {
        return $this->hasMany(AccesorioListado::class);
    }
}
