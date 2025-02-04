<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'slug',
        'duration',
        'total_marks',
        'pass_marks',
        'certificate_included',
        'status',
    ];

    public function section(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function result(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(Result::class, 'quiz_id', 'id')->where('user_id', auth()->user()->id);
    }

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function quizAnswer()
    {
        return $this->hasMany(QuizAnswer::class, 'quiz_id', 'id');
    }
}
