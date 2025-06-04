<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScratchCard extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id', 'company_id', 'is_done', 'gift_id'];

    public function gift() {
        return $this->belongsTo(ScratchCardGift::class, 'gift_id');
    }
}
