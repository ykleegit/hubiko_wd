<?php

namespace Hubiko\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\User;
use Hubiko\Tags\Entities\Tags;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'name',
        'email',
        'mobile_no',
        'category_id',
        'priority',
        'subject',
        'status',
        'is_assign',
        'description',
        'created_by',
        'attachments',
        'note',
        'type',
        'workspace',
        'tags_id',
        'reslove_at',
    ];

    protected $casts = [
        'reslove_at' => 'datetime',
        'attachments' => 'array',
    ];

    public static $statues = [
        'New Ticket',
        'In Progress',
        'On Hold',
        'Closed',
        'Resolved',
    ];

    // Scopes for workspace and user filtering
    public function scopeWorkspace($query, $workspace = null)
    {
        $workspace = $workspace ?? getActiveWorkSpace();
        return $query->where('workspace', $workspace);
    }

    public function scopeCreatedBy($query, $createdBy = null)
    {
        $createdBy = $createdBy ?? creatorId();
        return $query->where('created_by', $createdBy);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['New Ticket', 'In Progress', 'On Hold']);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['Closed', 'Resolved']);
    }

    public function conversions()
    {
        return $this->hasMany('Hubiko\Ticket\Entities\Conversion', 'ticket_id', 'id')->orderBy('id');
    }

    public function getAgentDetails(){
        return $this->hasOne(User::class, 'id', 'is_assign');
    }
    
    public function getCategory()
    {
        return $this->hasOne('Hubiko\Ticket\Entities\Category', 'id', 'category_id');
    }

    public function getPriority()
    {
        return $this->hasOne('Hubiko\Ticket\Entities\Priority', 'id', 'priority');
    }

    public function getTicketCreatedBy(){
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public static function category($category)
    {
        $unitRate = 0;
        $category = Category::find($category);
        if ($category) {
            $unitRate = $category->name;
        } else {
            $unitRate = '-'; 
        }
        return $unitRate;
    }

    public static function getIncExpLineChartDate()
    {
        $m = date("m");
        $de = date("d");
        $y = date("Y");
        $format = 'Y-m-d';
        $arrDate = [];
        $arrDateFormat = [];

        for($i = 7; $i >= 0; $i--)
        {
            $date = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));

            $arrDay[] = date('D', mktime(0, 0, 0, $m, ($de - $i), $y));
            $arrDate[] = $date;
            $arrDateFormat[] = date("d", strtotime($date)) .'-'.__(date("M", strtotime($date)));
        }
        $data['day'] = $arrDateFormat;

        $open_ticket = array();
        $close_ticket = array();

        for($i = 0; $i < count($arrDate); $i++)
        {
            $aopen_ticket = Ticket::whereIn('status', ['On Hold','In Progress'])
                ->where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->whereDate('created_at', $arrDate[$i])
                ->get();
            $open_ticket[] = count($aopen_ticket);

            $aclose_ticket = Ticket::where('status', '=', 'Closed')
                ->where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->whereDate('created_at', $arrDate[$i])
                ->get();
            $close_ticket[] = count($aclose_ticket);
        }

        $data['open_ticket'] = $open_ticket;
        $data['close_ticket'] = $close_ticket;

        return $data;
    }

    // Helper methods for ticket management
    public function isOpen()
    {
        return in_array($this->status, ['New Ticket', 'In Progress', 'On Hold']);
    }

    public function isClosed()
    {
        return in_array($this->status, ['Closed', 'Resolved']);
    }

    public function getStatusBadgeClass()
    {
        $classes = [
            'New Ticket' => 'badge-primary',
            'In Progress' => 'badge-warning',
            'On Hold' => 'badge-secondary',
            'Closed' => 'badge-danger',
            'Resolved' => 'badge-success',
        ];
        
        return $classes[$this->status] ?? 'badge-light';
    }

    public function getPriorityBadgeClass()
    {
        $priority = $this->getPriority;
        if (!$priority) return 'badge-light';
        
        $classes = [
            'Low' => 'badge-info',
            'Medium' => 'badge-warning',
            'High' => 'badge-danger',
            'Critical' => 'badge-dark',
        ];
        
        return $classes[$priority->name] ?? 'badge-light';
    }

    public static function getTicketTypes() {
        $ticketTypes = [
            'Unassigned',
            'Assigned',
        ];
        
        if(module_is_active('WhatsAppChatBotAndChat')) {
            $ticketTypes[] = 'Whatsapp';
        }
        if(module_is_active('InstagramChat')) {
            $ticketTypes[] = 'Instagram';
        }
        if(module_is_active('FacebookChat')) {
            $ticketTypes[] = 'Facebook';
        }
        
        return $ticketTypes;
    }

    public function messages(){
        return $this->hasMany('Hubiko\Ticket\Entities\Conversion', 'ticket_id', 'id');
    }

    public function unreadMessge($id)
    {
        return $this->messages()->where([
            'ticket_id' => $id,
            'is_read' => 0
        ]);
    }

    public function latestMessages($id)
    {
        return $this->messages()->where('ticket_id', $id)->latest()->first();
    }

    public function getTagsAttribute()
    {
        $tagsIds = explode(',', $this->tags_id ?? '');
        if(empty($tagsIds[0])) {
            return [];
        }
        
        if(module_is_active('Tags')) {
            return Tags::whereIn('id', $tagsIds)->get();
        }
        
        return [];
    }
} 