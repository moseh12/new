<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveClass extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'title', 'slug', 'meeting_method', 'start_time', 'end_time', 'duration', 'data', 'meeting_link', 'status', 'meeting_type', 'meeting_interval', 'days'];

    protected $casts    = [
        'data'         => 'array',
        'meeting_link' => 'array',
        'days'         => 'array',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
