<?php

namespace Hubiko\Booking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Hubiko\Booking\Entities\Room;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (\Auth::user()->can('room manage')) {
            $rooms = Room::byWorkspace(getActiveWorkSpace())
                ->when($request->status, function($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->room_type, function($query, $type) {
                    return $query->where('room_type', $type);
                })
                ->when($request->search, function($query, $search) {
                    return $query->where('room_number', 'like', "%{$search}%")
                                 ->orWhere('room_type', 'like', "%{$search}%");
                })
                ->orderBy('room_number')
                ->paginate(15);

            return view('booking::rooms.index', compact('rooms'));
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
        if (\Auth::user()->can('room create')) {
            return view('booking::rooms.create');
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
        if (\Auth::user()->can('room create')) {
            $request->validate([
                'room_number' => 'required|string|unique:rooms,room_number',
                'room_type' => 'required|string|max:100',
                'floor_number' => 'nullable|integer|min:0',
                'capacity' => 'required|integer|min:1',
                'price_per_night' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'amenities' => 'nullable|array',
                'status' => 'required|in:available,occupied,maintenance,out_of_order',
            ]);

            Room::create([
                'room_number' => $request->room_number,
                'room_type' => $request->room_type,
                'floor_number' => $request->floor_number,
                'capacity' => $request->capacity,
                'price_per_night' => $request->price_per_night,
                'description' => $request->description,
                'amenities' => $request->amenities ?? [],
                'status' => $request->status,
                'created_by' => \Auth::user()->id,
                'workspace' => getActiveWorkSpace(),
            ]);

            return redirect()->route('rooms.index')->with('success', __('Room created successfully.'));
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
        if (\Auth::user()->can('room show')) {
            $room = Room::with(['bookings' => function($query) {
                $query->with('guest')->orderBy('check_in_date', 'desc');
            }])->findOrFail($id);
            
            return view('booking::rooms.show', compact('room'));
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
        if (\Auth::user()->can('room edit')) {
            $room = Room::findOrFail($id);
            return view('booking::rooms.edit', compact('room'));
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
        if (\Auth::user()->can('room edit')) {
            $room = Room::findOrFail($id);
            
            $request->validate([
                'room_number' => 'required|string|unique:rooms,room_number,' . $room->id,
                'room_type' => 'required|string|max:100',
                'floor_number' => 'nullable|integer|min:0',
                'capacity' => 'required|integer|min:1',
                'price_per_night' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'amenities' => 'nullable|array',
                'status' => 'required|in:available,occupied,maintenance,out_of_order',
            ]);

            $room->update([
                'room_number' => $request->room_number,
                'room_type' => $request->room_type,
                'floor_number' => $request->floor_number,
                'capacity' => $request->capacity,
                'price_per_night' => $request->price_per_night,
                'description' => $request->description,
                'amenities' => $request->amenities ?? [],
                'status' => $request->status,
            ]);

            return redirect()->route('rooms.index')->with('success', __('Room updated successfully.'));
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
        if (\Auth::user()->can('room delete')) {
            $room = Room::findOrFail($id);
            
            // Check if room has active bookings
            if ($room->bookings()->active()->count() > 0) {
                return redirect()->back()->with('error', __('Cannot delete room with active bookings.'));
            }
            
            $room->delete();

            return redirect()->route('rooms.index')->with('success', __('Room deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show room availability
     */
    public function availability($id, Request $request)
    {
        if (\Auth::user()->can('room show')) {
            $room = Room::findOrFail($id);
            $month = $request->get('month', now()->format('Y-m'));
            
            $bookings = $room->bookings()
                ->whereYear('check_in_date', '<=', substr($month, 0, 4))
                ->whereMonth('check_in_date', '<=', substr($month, 5, 2))
                ->whereYear('check_out_date', '>=', substr($month, 0, 4))
                ->whereMonth('check_out_date', '>=', substr($month, 5, 2))
                ->with('guest')
                ->get();
            
            return view('booking::rooms.availability', compact('room', 'bookings', 'month'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
