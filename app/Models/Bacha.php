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

    public function bachasStock()
    {
        return $this->hasMany(BachaListado::class);
    }

    public function bachas_selections()
    {
        return $this->hasMany(BachasSelection::class);
    }
}
