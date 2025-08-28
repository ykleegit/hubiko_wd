<?php

namespace Hubiko\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Conversion extends Model
{
    use HasFactory;

    protected $table = 'ticket_conversions';

    protected $fillable = [
        'ticket_id',
        'description', 
        'attachments', 
        'sender',
        'is_read',
        'workspace',
        'created_by'
    ];

    public function replyBy(){
        if($this->sender == 'user'){
            return $this->ticket();
        } else {
            return $this->hasOne(User::class, 'id', 'sender')->first();
        }
    }

    public function ticket(){
        return $this->hasOne('Hubiko\Ticket\Entities\Ticket', 'id', 'ticket_id');
    }

    public static function change_status($ticket_id)
    {
        $ticket = Ticket::find($ticket_id);
        if ($ticket) {
            $ticket->status = 'In Progress';
            $ticket->update();
            return $ticket;
        }
        return null;
    }
} 