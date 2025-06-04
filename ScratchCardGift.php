<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScratchCardGift extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'media'];

    public function scratchCards() {
        return $this->hasMany(ScratchCard::class, 'gift_id');
    }
}
