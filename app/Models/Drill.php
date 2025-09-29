<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'action_type',
        'criteria',
    ];

    protected $casts = [
        'criteria' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
