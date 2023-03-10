<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BachaListado extends Model
{
    use HasFactory;

    protected $fillable = [
        'linea', 'modelo', 'stock'
    ];

    protected $guarded = [
        'id', 'bacha_id'
    ];

    public function bachas()
    {
        return $this->belongsToMany(Bacha::class);
    }

    public function bachas_selections()
    {
        return $this->belongsTo(BachasSelection::class);
    }
}
