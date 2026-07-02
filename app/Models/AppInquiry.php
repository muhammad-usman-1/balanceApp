<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppInquiry extends Model
{
    protected $table = 'app_inquiries';

    protected $fillable = [
        'user_id', 'name', 'email', 'mobile',
        'subject', 'description',
        'status', 'admin_reply', 'replied_by', 'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function repliedBy()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function scopeUnread($query)
    {
        return $query->where('status', 'new');
    }
}
