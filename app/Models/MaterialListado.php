<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialListado extends Model
{
    use HasFactory;

    protected $fillable = [
        'material', 'size', 'stock'
    ];

    protected $guarded = [
        'id', 'material_id'
    ];

    public function materiales()
    {
        return $this->belongsToMany(Material::class);
    }

    public function materiales_selections()
    {
        return $this->belongsTo(MaterialesSelection::class);
    }
}
