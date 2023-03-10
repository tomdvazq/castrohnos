<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BachasSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        "pedido_id", "bacha_id", "bachas_listados_id", "cantidad", "material"
    ];

    protected $guarded = [
        'id'
    ];
    
    public function pedidos()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
    

    public function bachas()
    {
        return $this->belongsTo(Bacha::class, 'bacha_id');
    }

    public function bachasStock()
    {
        return $this->hasMany(BachaListado::class);
    }
}
