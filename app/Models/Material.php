<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'tipo'
    ];

    protected $guarded = [
        'id'
    ];

    public function materialesStock()
    {
        return $this->hasMany(MaterialListado::class);
    }

    public function materiales_selections()
    {
        return $this->hasMany(MaterialesSelection::class, 'tipo');
    }
}
