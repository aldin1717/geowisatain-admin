<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'base_price', 'capacity', 'image', 'is_active'];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
