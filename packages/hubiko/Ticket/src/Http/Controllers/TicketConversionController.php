<?php

namespace Hubiko\Ticket\Http\Controllers;

use App\Events\TicketReply;
use App\Events\UpdateTicketStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;
use Hubiko\Ticket\Entities\Category;
use Hubiko\Ticket\Entities\Conversion;
use Hubiko\Ticket\Entities\CustomField;
use Hubiko\Ticket\Entities\Priority;
use Hubiko\Ticket\Entities\Ticket;

class TicketConversionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {        
        if (Auth::user()->isAbleTo('ticket manage')) {
            $tikcettype = Ticket::getTicketTypes();
            $settings = getCompanyAllSettings();

            if (Auth::user()->hasRole('admin')) {
                $tickets = Ticket::with('getAgentDetails', 'getCategory', 'getPriority', 'getTicketCreatedBy')
                    ->where('workspace', getActiveWorkSpace());
            } elseif (Auth::user()->hasRole('customer')) {
                $tickets = Ticket::with('getAgentDetails', 'getCategory', 'getPriority', 'getTicketCreatedBy')
                    ->where('workspace', getActiveWorkSpace())
                    ->where('email', Auth::user()->email);
            } else {
                $tickets = Ticket::with('getAgentDetails', 'getCategory', 'getPriority', 'getTicketCreatedBy')
                    ->where('workspace', getActiveWorkSpace())
                    ->where(function ($query) {
                        $query->where('is_assign', Auth::user()->id)
                              ->orWhere('created_by', Auth::user()->id);
                    });            
            }

            if ($request->tikcettype != null) {
                $tickets->where('type', $request->tikcettype);
            }
            
            if ($request->priority != null) {
                $tickets->where('priority', $request->priority);
            }
            
            if ($request->status != null) {
                $tickets->where('status', $request->status);
            }
            
            if ($request->tags != null) {
                $tickets->whereRaw("FIND_IN_SET(?, tags_id)", [$request->tags]);
            }
            
            $tickets = $tickets->orderBy('id', 'desc')->get();

            $totalticket = $tickets->count();
            $ticketsWithMessages = $tickets->map(function ($ticket) {
                $latestMessage = $ticket->latestMessages($ticket->id);
                $unreadMessageCount = $ticket->unreadMessge($ticket->id)->count();
                $ticket->tag = $ticket->getTagsAttribute();
                $ticket->latest_message = $latestMessage;
                $ticket->unread = $unreadMessageCount;
                $ticket->ticket_id = module_is_active('TicketNumber') ? \Hubiko\TicketNumber\Entities\TicketNumber::ticketNumberFormat($ticket->id) : $ticket->ticket_id;
                return $ticket;
            });

            if ($request->ajax()) {
                // Return the tickets along with the latest message and unread count
                return response()->json([
                    'tickets' => $ticketsWithMessages, // Use the processed ticketsWithMessages
                ]);
            }
            $priorities = Priority::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();

            return view('ticket::chats.new-chat', compact('tickets', 'tikcettype', 'totalticket', 'settings', 'priorities'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getallTicket(Request $request)
    {
        if (!Auth::user()->isAbleTo('ticket manage')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
        
        $tickets = Ticket::where('id', '<', $request->lastTicketId)
            ->where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();
            
        $ticketsWithMessages = $tickets->map(function ($ticket) {
            $latestMessage = $ticket->latestMessages($ticket->id);
            $unreadMessageCount = $ticket->unreadMessge($ticket->id)->count();
            $ticket->tag = $ticket->getTagsAttribute();
            $ticket->latest_message = $latestMessage;
            $ticket->unread = $unreadMessageCount;
            $ticket->ticket_id = module_is_active('TicketNumber') ? \Hubiko\TicketNumber\Entities\TicketNumber::ticketNumberFormat($ticket->id) : $ticket->ticket_id;
            return $ticket;
        });

        return response()->json([
            'tickets' => $ticketsWithMessages,
        ]);
    }

    public function getticketDetails($ticket_id)
    {
        if (!Auth::user()->isAbleTo('ticket manage')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        $ticket = Ticket::with('conversions')
            ->where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($ticket_id);

        if ($ticket) {
            $conversions = Conversion::where('ticket_id', $ticket_id)->get();
            foreach ($conversions as $conversion) {
                $conversion = Conversion::find($conversion->id);
                $conversion->is_read = 1;
                $conversion->update();
            }

            $status = $ticket->status;
            $users = User::where('type', 'agent')
                ->where('created_by', creatorId())
                ->get();
                
            $categories = Category::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $categoryTree = buildCategoryTree($categories);
            
            $priorities = Priority::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $tikcettype = Ticket::getTicketTypes();
            
            $customFields = CustomField::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $settings = getCompanyAllSettings();

            $tickethtml = view('ticket::chats.new-chat-messge', compact(
                'ticket', 
                'users', 
                'categoryTree', 
                'priorities', 
                'tikcettype', 
                'customFields', 
                'settings'
            ))->render();

            $response = [
                'tickethtml' => $tickethtml,
                'status'     => $status,
                'unread_message_count' => $ticket->unreadMessge($ticket_id)->count(),
                'tag' => $ticket->getTagsAttribute(),
            ];
            
            return json_encode($response);
        } else {
            $response['status'] = 'error';
            $response['message'] = __('Ticket not found');
            return $response;
        }
    }

    public function statusChange(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('ticket edit')) {
            $status = $request->status;
            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($id);
                
            $settings = getCompanyAllSettings();
            
            if ($ticket) {
                $ticket->status = $status;
                if ($status == 'Resolved') {
                    $ticket->reslove_at = now();
                }
                $ticket->save();
                
                event(new UpdateTicketStatus($ticket, $request));
                
                if ($status == 'Closed') {
                    // Send Email To The Ticket User
                    $error_msg = '';
                    sendTicketEmail('Ticket Close', $settings, $ticket, $ticket, $error_msg);
                }

                $data['status'] = 'success';
                $data['message'] = __('Ticket status changed successfully.');
                return $data;
            } else {
                $data['status'] = 'error';
                $data['message'] = __('Ticket not found');
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = __('Permission Denied.');
            return $data;
        }
    }

    public function replystore(Request $request, $ticket_id)
    {
        $user = Auth::user();

        if ($user->can('ticket reply')) {
            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($ticket_id);
                
            $description = $request->reply_description;

            if ($ticket) {
                if ($description !== null || $request->hasfile('reply_attachments')) {
                    if ($ticket->type === 'Whatsapp' && module_is_active('WhatsAppChatBotAndChat')) {
                        $whatsappController = new \Hubiko\WhatsAppChatBotAndChat\Http\Controllers\SendWhatsAppMessageController();
                        $response = $whatsappController->sendMessage($request, $ticket, $user);
                        return $response;
                    } elseif ($ticket->type === 'Instagram' && module_is_active('InstagramChat')) {
                        $instagramController = new \Hubiko\InstagramChat\Http\Controllers\SendInstagramMessageController();
                        $response = $instagramController->sendMessage($request, $ticket, $user);
                        return $response;
                    } elseif ($ticket->type === 'Facebook' && module_is_active('FacebookChat')) {
                        $facebookController = new \Hubiko\FacebookChat\Http\Controllers\SendFacebookMessageController();
                        $response = $facebookController->sendMessage($request, $ticket, $user);
                        return $response;
                    } else {
                        if ($request->hasfile('reply_attachments')) {
                            $validation['reply_attachments.*'] = 'mimes:zip,rar,jpeg,jpg,png,gif,svg,pdf,txt,doc,docx,application/octet-stream,audio/mpeg,mpga,mp3,wav|max:204800';
                            $this->validate($request, $validation);
                        }

                        $conversion = new Conversion();
                        if (module_is_active('CustomerLogin') && Auth::user()->hasRole('customer')) {
                            $conversion->sender = 'user';
                        } else {
                            $conversion->sender = isset($user) ? $user->id : 'user';
                        }
                        $conversion->ticket_id = $ticket->id;
                        $conversion->description = $request->reply_description;
                        $conversion->workspace = getActiveWorkSpace();
                        $conversion->created_by = creatorId();

                        // Handle file upload
                        $data = $this->handleFileUpload($request, $ticket);
                        $conversion->attachments = json_encode($data);
                        $conversion->save();

                        // Update ticket status
                        if ($ticket->status == 'New Ticket') {
                            $ticket->status = 'In Progress';
                            $ticket->save();
                        }

                        // Handle Pusher notification and email
                        $this->managePusherAndEmailNotification($conversion, $ticket, $request);

                        // Trigger event
                        event(new TicketReply($conversion));

                        $response['status'] = 'success';
                        $response['message'] = __('Message send successfully');
                        $response['reply_description'] = $conversion->description;
                        return $response;
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = __('Message field is required.');
                    return $response;
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = __('Ticket not found');
                return $response;
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = __('Permission Denied.');
            return $response;
        }
    }

    protected function handleFileUpload(Request $request, $ticket)
    {
        $data = [];
        if ($request->hasfile('reply_attachments')) {
            foreach ($request->file('reply_attachments') as $filekey => $file) {
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir = ('tickets/' . $ticket->ticket_id);
                $path = multipleFileUpload($file, 'reply_attachments', $fileNameToStore, $dir);

                if ($path['flag'] == 1) {
                    $data[] = $path['url'];
                }
            }
        }
        return $data;
    }

    protected function managePusherAndEmailNotification($conversion, $ticket, $request)
    {
        // Send email notification
        $settings = getCompanyAllSettings();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('company') || Auth::user()->hasRole('agent')) {
            $error_msg = '';
            sendTicketEmail('Send Ticket Reply', $settings, $ticket, $request, $error_msg);
        }

        // Send Pusher notification if enabled
        if (isset($settings['pusher_status']) && $settings['pusher_status'] == 'on') {
            $options = array(
                'cluster' => $settings['pusher_cluster'],
                'useTLS' => true,
            );

            $pusher = new Pusher(
                $settings['pusher_app_key'],
                $settings['pusher_app_secret'],
                $settings['pusher_app_id'],
                $options
            );

            $data = [];
            $data['ticket_id'] = $ticket->id;
            $data['conversion_id'] = $conversion->id;
            $pusher->trigger('my-channel', 'my-event', $data);
        }
    }

    public function storeNote(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('ticket edit')) {
            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($id);
                
            if ($ticket) {
                $validator = Validator::make($request->all(), [
                    'note' => 'required',
                ]);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return response()->json(['error' => $messages->first()]);
                }

                $ticket->note = $request->note;
                $ticket->save();

                return response()->json(['success' => __('Note added successfully.')]);
            } else {
                return response()->json(['error' => __('Ticket not found')]);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')]);
        }
    }

    public function assignChange(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('ticket edit')) {
            $agent = $request->agent;
            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($id);
                
            if ($ticket) {
                $ticket->is_assign = $agent;
                $ticket->save();

                $data['status'] = 'success';
                $data['message'] = __('Agent assigned successfully.');
                return $data;
            } else {
                $data['status'] = 'error';
                $data['message'] = __('Ticket not found');
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = __('Permission Denied.');
            return $data;
        }
    }

    public function categoryChange(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('ticket edit')) {
            $category = $request->category;
            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($id);
                
            if ($ticket) {
                $ticket->category_id = $category;
                $ticket->save();

                $data['status'] = 'success';
                $data['message'] = __('Category changed successfully.');
                return $data;
            } else {
                $data['status'] = 'error';
                $data['message'] = __('Ticket not found');
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = __('Permission Denied.');
            return $data;
        }
    }

    public function priorityChange(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('ticket edit')) {
            $priority = $request->priority;
            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($id);
                
            if ($ticket) {
                $ticket->priority = $priority;
                $ticket->save();

                $data['status'] = 'success';
                $data['message'] = __('Priority changed successfully.');
                return $data;
            } else {
                $data['status'] = 'error';
                $data['message'] = __('Ticket not found');
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = __('Permission Denied.');
            return $data;
        }
    }

    public function ticketnameChange(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('ticket edit')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()]);
            }

            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($id);
                
            if ($ticket) {
                $ticket->name = $request->name;
                $ticket->save();

                $data['status'] = 'success';
                $data['message'] = __('Name changed successfully.');
                return $data;
            } else {
                $data['status'] = 'error';
                $data['message'] = __('Ticket not found');
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = __('Permission Denied.');
            return $data;
        }
    }

    public function ticketemailChange(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('ticket edit')) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()]);
            }

            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($id);
                
            if ($ticket) {
                $ticket->email = $request->email;
                $ticket->save();

                $data['status'] = 'success';
                $data['message'] = __('Email changed successfully.');
                return $data;
            } else {
                $data['status'] = 'error';
                $data['message'] = __('Ticket not found');
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = __('Permission Denied.');
            return $data;
        }
    }

    public function ticketsubChange(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('ticket edit')) {
            $validator = Validator::make($request->all(), [
                'subject' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()]);
            }

            $ticket = Ticket::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->find($id);
                
            if ($ticket) {
                $ticket->subject = $request->subject;
                $ticket->save();

                $data['status'] = 'success';
                $data['message'] = __('Subject changed successfully.');
                return $data;
            } else {
                $data['status'] = 'error';
                $data['message'] = __('Ticket not found');
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = __('Permission Denied.');
            return $data;
        }
    }

    public function readmessge($ticket_id)
    {
        if (!Auth::user()->isAbleTo('ticket manage')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        $ticket = Ticket::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($ticket_id);
            
        if ($ticket) {
            $conversions = Conversion::where('ticket_id', $ticket_id)->get();
            
            foreach ($conversions as $conversion) {
                $conversion = Conversion::find($conversion->id);
                $conversion->is_read = 1;
                $conversion->update();
            }

            $data['status'] = 'success';
            $data['message'] = __('Message read status updated successfully.');
            return $data;
        } else {
            $data['status'] = 'error';
            $data['message'] = __('Ticket not found');
            return $data;
        }
    }

    public function ticketcustomfield($id)
    {
        if (!Auth::user()->isAbleTo('ticket manage')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        $ticket = Ticket::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($id);
            
        if ($ticket) {
            $customFields = CustomField::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $htmls = '';
            
            foreach ($customFields as $field) {
                $htmls .= CustomField::prepareCustomRendering($field, true, $ticket);
            }
            
            return $htmls;
        } else {
            return response()->json(['error' => __('Ticket not found')], 404);
        }
    }

    public function ticketcustomfieldUpdate(Request $request, $ticket_id)
    {
        if (!Auth::user()->isAbleTo('ticket edit')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        $ticket = Ticket::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($ticket_id);
            
        if ($ticket) {
            CustomField::saveData($ticket, $request->customField);
            return response()->json(['success' => __('Custom fields updated successfully.')]);
        } else {
            return response()->json(['error' => __('Ticket not found')], 404);
        }
    }
} 