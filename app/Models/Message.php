<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\EmailHelper;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'chat_id',
        'user_id',
        'subject',
        'message',
        'status',
    ];

    protected $with = [
        'files'
    ];

    public function files() : HasMany
    {
        return $this->hasMany(MessageFile::class);
    }
}
