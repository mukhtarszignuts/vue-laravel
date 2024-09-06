<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'unseenMsgs',
        'feedback',
        'is_sent',
        'is_delivered',
        'is_seen',
    ];
    
    // sender
    public function sender(){
        return $this->belongsTo(User::class,'sender_id');
    }
    // receiver
    public function receiver(){
        return $this->belongsTo(User::class,'receiver_id');
    }    
}
