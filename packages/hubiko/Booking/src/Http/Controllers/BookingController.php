<?php

namespace Hubiko\Booking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Hubiko\Booking\Entities\Booking;
use Hubiko\Booking\Entities\Guest;
use Hubiko\Booking\Entities\Room;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (\Auth::user()->can('booking manage')) {
            $bookings = Booking::with(['guest', 'room'])
                ->byWorkspace(getActiveWorkSpace())
                ->when($request->status, function($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->search, function($query, $search) {
                    return $query->whereHas('guest', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    })->orWhere('booking_number', 'like', "%{$search}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('booking::bookings.index', compact('bookings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (\Auth::user()->can('booking create')) {
            $guests = Guest::byWorkspace(getActiveWorkSpace())->get();
            $rooms = Room::byWorkspace(getActiveWorkSpace())->available()->get();
            
            return view('booking::bookings.create', compact('guests', 'rooms'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('booking create')) {
            $request->validate([
                'guest_id' => 'required|exists:guests,id',
                'room_id' => 'required|exists:rooms,id',
                'check_in_date' => 'required|date|after_or_equal:today',
                'check_out_date' => 'required|date|after:check_in_date',
                'adults' => 'required|integer|min:1',
                'children' => 'integer|min:0',
                'total_amount' => 'required|numeric|min:0',
            ]);

            // Check room availability
            $room = Room::find($request->room_id);
            if (!$room->isAvailableForDates($request->check_in_date, $request->check_out_date)) {
                return redirect()->back()->with('error', __('Room is not available for selected dates.'));
            }

            $booking = Booking::create([
                'guest_id' => $request->guest_id,
                'room_id' => $request->room_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'adults' => $request->adults,
                'children' => $request->children ?? 0,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->paid_amount ?? 0,
                'payment_status' => $request->paid_amount >= $request->total_amount ? 'paid' : 'pending',
                'status' => 'confirmed',
                'special_requests' => $request->special_requests,
                'notes' => $request->notes,
                'created_by' => \Auth::user()->id,
                'workspace' => getActiveWorkSpace(),
            ]);

            return redirect()->route('bookings.index')->with('success', __('Booking created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if (\Auth::user()->can('booking show')) {
            $booking = Booking::with(['guest', 'room'])->findOrFail($id);
            return view('booking::bookings.show', compact('booking'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (\Auth::user()->can('booking edit')) {
            $booking = Booking::findOrFail($id);
            $guests = Guest::byWorkspace(getActiveWorkSpace())->get();
            $rooms = Room::byWorkspace(getActiveWorkSpace())->available()->get();
            
            return view('booking::bookings.edit', compact('booking', 'guests', 'rooms'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('booking edit')) {
            $booking = Booking::findOrFail($id);
            
            $request->validate([
                'guest_id' => 'required|exists:guests,id',
                'room_id' => 'required|exists:rooms,id',
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date|after:check_in_date',
                'adults' => 'required|integer|min:1',
                'children' => 'integer|min:0',
                'total_amount' => 'required|numeric|min:0',
            ]);

            $booking->update($request->all());

            return redirect()->route('bookings.index')->with('success', __('Booking updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('booking delete')) {
            $booking = Booking::findOrFail($id);
            $booking->delete();

            return redirect()->route('bookings.index')->with('success', __('Booking deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show booking calendar
     */
    public function calendar()
    {
        if (\Auth::user()->can('booking manage')) {
            $bookings = Booking::with(['guest', 'room'])
                ->byWorkspace(getActiveWorkSpace())
                ->active()
                ->get();

            return view('booking::bookings.calendar', compact('bookings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show booking dashboard
     */
    public function dashboard()
    {
        if (\Auth::user()->can('booking manage')) {
            $stats = [
                'total_bookings' => Booking::byWorkspace(getActiveWorkSpace())->count(),
                'active_bookings' => Booking::byWorkspace(getActiveWorkSpace())->active()->count(),
                'checked_in_today' => Booking::byWorkspace(getActiveWorkSpace())
                    ->whereDate('check_in_date', today())->count(),
                'checking_out_today' => Booking::byWorkspace(getActiveWorkSpace())
                    ->whereDate('check_out_date', today())->count(),
            ];

            $recent_bookings = Booking::with(['guest', 'room'])
                ->byWorkspace(getActiveWorkSpace())
                ->latest()
                ->limit(10)
                ->get();

            return view('booking::dashboard', compact('stats', 'recent_bookings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Process check-in
     */
    public function processCheckIn(Request $request, $id)
    {
        if (\Auth::user()->can('booking manage')) {
            $booking = Booking::findOrFail($id);
            
            if ($booking->canCheckIn()) {
                $booking->update([
                    'status' => Booking::STATUS_CHECKED_IN,
                    'actual_check_in' => now(),
                ]);

                // Update room status
                $booking->room->update(['status' => Room::STATUS_OCCUPIED]);

                return redirect()->back()->with('success', __('Guest checked in successfully.'));
            }

            return redirect()->back()->with('error', __('Cannot check in this booking.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Process check-out
     */
    public function processCheckOut(Request $request, $id)
    {
        if (\Auth::user()->can('booking manage')) {
            $booking = Booking::findOrFail($id);
            
            if ($booking->canCheckOut()) {
                $booking->update([
                    'status' => Booking::STATUS_CHECKED_OUT,
                    'actual_check_out' => now(),
                ]);

                // Update room status
                $booking->room->update(['status' => Room::STATUS_AVAILABLE]);

                return redirect()->back()->with('success', __('Guest checked out successfully.'));
            }

            return redirect()->back()->with('error', __('Cannot check out this booking.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
