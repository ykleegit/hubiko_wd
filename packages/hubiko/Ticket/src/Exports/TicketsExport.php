<?php

namespace Hubiko\Ticket\Exports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Hubiko\Ticket\Entities\Ticket;

class TicketsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = Ticket::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->get();
            
        $tickets = [];
        
        foreach ($data as $k => $ticket) {
            $tickets[] = [
                'ID' => $ticket->id,
                'Ticket ID' => $ticket->ticket_id,
                'Name' => $ticket->name,
                'Email' => $ticket->email,
                'Subject' => $ticket->subject,
                'Category' => !empty($ticket->getCategory) ? $ticket->getCategory->name : '',
                'Status' => $ticket->status,
                'Priority' => !empty($ticket->getPriority) ? $ticket->getPriority->name : '',
                'Created Date' => \Carbon\Carbon::parse($ticket->created_at)->format('d-m-Y'),
                'Updated Date' => \Carbon\Carbon::parse($ticket->updated_at)->format('d-m-Y'),
            ];
        }
        
        return collect($tickets);
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Ticket ID',
            'Name',
            'Email',
            'Subject',
            'Category',
            'Status',
            'Priority',
            'Created Date',
            'Updated Date',
        ];
    }
} 