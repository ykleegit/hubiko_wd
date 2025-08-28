<?php

namespace Hubiko\Ticket\Http\Controllers;

use App\Events\CreateTicket;
use App\Events\DestroyTicket;
use App\Events\UpdateTicket;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Hubiko\Ticket\Entities\Category;
use Hubiko\Ticket\Entities\Conversion;
use Hubiko\Ticket\Entities\CustomField;
use Hubiko\Ticket\Entities\Priority;
use Hubiko\Ticket\Entities\Ticket;
use Hubiko\Ticket\Exports\TicketsExport;

class TicketController extends Controller
{
    public function __construct()
    {
        if (config('2fa_enabled')) {
            $this->middleware('2fa');
        }
    }

    public function dashboard()
    {
        if (Auth::user()->isAbleTo('ticket manage')) {
            $stats = [
                'total' => Ticket::workspace()->createdBy()->count(),
                'open' => Ticket::workspace()->createdBy()->open()->count(),
                'closed' => Ticket::workspace()->createdBy()->status('Closed')->count(),
                'resolved' => Ticket::workspace()->createdBy()->status('Resolved')->count(),
            ];
            
            $chartData = Ticket::getIncExpLineChartDate();
            
            return view('ticket::dashboard', compact('stats', 'chartData'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('ticket manage')) {
            $query = Ticket::with('getAgentDetails', 'getCategory', 'getPriority')
                ->workspace()
                ->createdBy();
                
            // Apply filters if any
            if ($request->has('category') && !empty($request->category)) {
                $query->where('category_id', $request->category);
            }
            
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('priority') && !empty($request->priority)) {
                $query->where('priority', $request->priority);
            }
            
            if ($request->has('agent') && !empty($request->agent)) {
                $query->where('is_assign', $request->agent);
            }
            
            $tickets = $query->orderBy('id', 'desc')->get();
            
            // Get filter options
            $categories = Category::workspace()->createdBy()->get();
            $priorities = Priority::workspace()->createdBy()->get();
            $agents = User::where('workspace_id', getActiveWorkSpace())->get();
                
            return view('ticket::ticket.index', compact('tickets', 'categories', 'priorities', 'agents'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->isAbleTo('ticket create')) {
            $customFields = CustomField::workspace()->createdBy()->get();
            $categories = Category::workspace()->createdBy()->get();
            $priorities = Priority::workspace()->createdBy()->get();
            $agents = User::where('workspace_id', getActiveWorkSpace())->get();
                
            $settings = getCompanyAllSettings();
            
            $users = User::where('type', 'agent')
                ->where('created_by', creatorId())
                ->get();
                
            $ticket = null;
            
            return view('ticket::ticket.create', compact('categories', 'customFields', 'priorities', 'settings', 'categoryTree', 'ticket', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('ticket create')) {
            $validation = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'category' => 'required',
                'priority' => 'required',
                'subject' => 'required|string|max:255',
                'status' => 'required|string|max:100',
                'description' => 'required',
                'priority' => 'required',
                'agent' => 'required',
            ];

            $this->validate($request, $validation);

            $ticket = new Ticket();
            $ticket->ticket_id = time();
            $ticket->name = $request->name;
            $ticket->email = $request->email;
            $ticket->mobile_no = $request->mobile_no;
            $ticket->category_id = $request->category;
            $ticket->is_assign = $request->agent;
            $ticket->priority = $request->priority;
            $ticket->subject = $request->subject;
            $ticket->status = $request->status;
            $ticket->description = $request->description;
            $ticket->type = "Assigned";
            $ticket->created_by = creatorId();
            $ticket->workspace = getActiveWorkSpace();
            
            $data = [];
            if ($request->hasfile('attachments')) {
                $errors = [];
                foreach ($request->file('attachments') as $filekey => $file) {
                    $filenameWithExt = $file->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $dir = ('tickets/' . $ticket->ticket_id);
                    $path = multipleFileUpload($file, 'attachments', $fileNameToStore, $dir);

                    if ($path['flag'] == 1) {
                        $data[] = $path['url'];
                    } elseif ($path['flag'] == 0) {
                        $errors = __($path['msg']);
                        return redirect()->back()->with('error', __($errors));
                    }
                }
            }
            $ticket->attachments = json_encode($data);
            $ticket->save();

            // Save custom fields
            CustomField::saveData($ticket, $request->customField);

            $settings = getCompanyAllSettings();

            // Trigger create ticket event
            event(new CreateTicket($ticket, $request));

            $error_msg = '';
            // Send Ticket Email
            sendTicketEmail('Send Mail To Agent', $settings, $ticket, $request, $error_msg);
            sendTicketEmail('Send Mail To Customer', $settings, $ticket, $request, $error_msg);
            sendTicketEmail('Send Mail To Admin', $settings, $ticket, $request, $error_msg);

            if (isset($error_msg)) {
                Session::put('smtp_error', '<br><span class="text-danger ml-2">' . $error_msg . '</span>');
            }

            Session::put('ticket_id', ' <a class="text text-primary" target="_blank" href="' . route('home.view', encrypt($ticket->ticket_id)) . '"><b>' . __('Your unique ticket link is this.') . '</b></a>');
            return redirect()->route('ticket.index')->with('success', __('Ticket created successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->isAbleTo('ticket edit')) {
            $ticket = Ticket::find($id);
            
            if ($ticket->workspace != getActiveWorkSpace() || $ticket->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
            
            $customFields = CustomField::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $categories = Category::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $categoryTree = buildCategoryTree($categories);
            
            $priorities = Priority::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $users = User::where('type', 'agent')
                ->where('created_by', creatorId())
                ->get();
                
            return view('ticket::ticket.edit', compact('ticket', 'categories', 'customFields', 'priorities', 'categoryTree', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('ticket edit')) {
            $ticket = Ticket::find($id);
            
            if ($ticket->workspace != getActiveWorkSpace() || $ticket->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
            
            $validation = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'category' => 'required',
                'priority' => 'required',
                'subject' => 'required|string|max:255',
                'status' => 'required|string|max:100',
                'description' => 'required',
                'agent' => 'required',
            ];

            $this->validate($request, $validation);

            $ticket->name = $request->name;
            $ticket->email = $request->email;
            $ticket->mobile_no = $request->mobile_no;
            $ticket->category_id = $request->category;
            $ticket->is_assign = $request->agent;
            $ticket->priority = $request->priority;
            $ticket->subject = $request->subject;
            $ticket->status = $request->status;
            $ticket->description = $request->description;
            
            if ($request->status == 'Resolved') {
                $ticket->reslove_at = now();
            }
            
            $data = json_decode($ticket->attachments, true) ?: [];
            if ($request->hasfile('attachments')) {
                $errors = [];
                foreach ($request->file('attachments') as $filekey => $file) {
                    $filenameWithExt = $file->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $dir = ('tickets/' . $ticket->ticket_id);
                    $path = multipleFileUpload($file, 'attachments', $fileNameToStore, $dir);

                    if ($path['flag'] == 1) {
                        $data[] = $path['url'];
                    } elseif ($path['flag'] == 0) {
                        $errors = __($path['msg']);
                        return redirect()->back()->with('error', __($errors));
                    }
                }
            }
            $ticket->attachments = json_encode($data);
            $ticket->save();

            // Save custom fields
            CustomField::saveData($ticket, $request->customField);

            // Trigger update ticket event
            event(new UpdateTicket($ticket, $request));

            return redirect()->route('ticket.index')->with('success', __('Ticket updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function show($id)
    {
        if (Auth::user()->isAbleTo('ticket show')) {
            $ticket = Ticket::with('getAgentDetails', 'getCategory', 'getPriority', 'conversions')
                ->where('id', $id)
                ->where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->first();
                
            if (!$ticket) {
                return redirect()->back()->with('error', __('Ticket not found.'));
            }
            
            return view('ticket::ticket.show', compact('ticket'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('ticket delete')) {
            $ticket = Ticket::with('conversions')->find($id);
            
            if (!$ticket || $ticket->workspace != getActiveWorkSpace() || $ticket->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Ticket Not Found.'));
            }
            
            // Trigger destroy ticket event
            event(new DestroyTicket($ticket));
            
            $ticketImageDirectory = ('uploads/tickets/' . $ticket->ticket_id);
            if (checkFile($ticketImageDirectory)) {
                File::deleteDirectory($ticketImageDirectory);
            }
            
            if ($ticket->conversions->isNotEmpty()) {
                $ticket->conversions->each(function ($conversion) {
                    $conversion->delete();
                });
            }
            
            $ticket->delete();

            return redirect()->back()->with('success', __('Ticket deleted successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function attachmentDestroy($ticket_id, $id)
    {
        if (Auth::user()->isAbleTo('ticket edit')) {
            $ticket = Ticket::find($ticket_id);
            
            if (!$ticket || $ticket->workspace != getActiveWorkSpace() || $ticket->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Ticket Not Found.'));
            }
            
            $attachments = json_decode($ticket->attachments);
            if (isset($attachments[$id])) {
                if (asset(Storage::exists('tickets/' . $ticket->ticket_id . "/" . $attachments[$id]))) {
                    asset(Storage::delete('tickets/' . $ticket->ticket_id . "/" . $attachments[$id]));
                }
                unset($attachments[$id]);
                $ticket->attachments = json_encode(array_values($attachments));
                $ticket->save();

                return redirect()->back()->with('success', __('Attachment deleted successfully'));
            } else {
                return redirect()->back()->with('error', __('Attachment is missing'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function reply(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('ticket reply')) {
            $ticket = Ticket::find($id);
            
            if (!$ticket || $ticket->workspace != getActiveWorkSpace() || $ticket->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Ticket Not Found.'));
            }
            
            $validation = [
                'description' => 'required',
                'status' => 'required|string|max:100',
            ];

            $this->validate($request, $validation);

            // Update ticket status if changed
            if ($ticket->status != $request->status) {
                $ticket->status = $request->status;
                
                if ($request->status == 'Resolved') {
                    $ticket->reslove_at = now();
                }
                
                $ticket->save();
            }

            // Create new conversion (reply)
            $conversion = new Conversion();
            $conversion->ticket_id = $ticket->id;
            $conversion->description = $request->description;
            $conversion->sender_id = Auth::user()->id;
            
            $data = [];
            if ($request->hasfile('attachments')) {
                $errors = [];
                foreach ($request->file('attachments') as $filekey => $file) {
                    $filenameWithExt = $file->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $dir = ('tickets/' . $ticket->ticket_id . '/replies');
                    $path = multipleFileUpload($file, 'attachments', $fileNameToStore, $dir);

                    if ($path['flag'] == 1) {
                        $data[] = $path['url'];
                    } elseif ($path['flag'] == 0) {
                        $errors = __($path['msg']);
                        return redirect()->back()->with('error', __($errors));
                    }
                }
            }
            $conversion->attachments = json_encode($data);
            $conversion->save();

            // Send notification email
            $settings = getCompanyAllSettings();
            $error_msg = '';
            
            // Send email to customer
            sendTicketEmail('Reply Mail To Customer', $settings, $ticket, $request, $error_msg);
            
            if (isset($error_msg)) {
                Session::put('smtp_error', '<br><span class="text-danger ml-2">' . $error_msg . '</span>');
            }

            return redirect()->back()->with('success', __('Reply added successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function export()
    {
        if (Auth::user()->isAbleTo('ticket export')) {
            $name = 'Tickets_' . date('Y-m-d_i_h_s');
            return Excel::download(new TicketsExport(), $name . '.csv');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
} 