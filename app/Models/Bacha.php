<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bacha extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca'
    ];

    protected $guarded = [
        'id'
    ];

    public function bachalistados()
    {
        return $this->belongsToMany(BachaListado::class);
    }
}
