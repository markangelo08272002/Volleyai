<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolleyballSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_path',
        'keypoints_json_path',
        'metrics',
        'ai_feedback',
        'manual_feedback',
        'status',
        'progress',
        'action_type',
        'grade',
    ];

    protected $casts = [
        'metrics' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
