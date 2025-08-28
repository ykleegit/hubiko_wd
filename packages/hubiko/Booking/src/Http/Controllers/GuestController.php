<?php

namespace Hubiko\Booking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Hubiko\Booking\Entities\Guest;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (\Auth::user()->can('guest manage')) {
            $guests = Guest::byWorkspace(getActiveWorkSpace())
                ->when($request->search, function($query, $search) {
                    return $query->search($search);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('booking::guests.index', compact('guests'));
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
        if (\Auth::user()->can('guest create')) {
            return view('booking::guests.create');
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
        if (\Auth::user()->can('guest create')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:guests,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'id_type' => 'nullable|in:passport,driver_license,national_id',
                'id_number' => 'nullable|string|max:50',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:male,female,other',
            ]);

            Guest::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'notes' => $request->notes,
                'created_by' => \Auth::user()->id,
                'workspace' => getActiveWorkSpace(),
            ]);

            return redirect()->route('guests.index')->with('success', __('Guest created successfully.'));
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
        if (\Auth::user()->can('guest show')) {
            $guest = Guest::with('bookings.room')->findOrFail($id);
            return view('booking::guests.show', compact('guest'));
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
        if (\Auth::user()->can('guest edit')) {
            $guest = Guest::findOrFail($id);
            return view('booking::guests.edit', compact('guest'));
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
        if (\Auth::user()->can('guest edit')) {
            $guest = Guest::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:guests,email,' . $guest->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'id_type' => 'nullable|in:passport,driver_license,national_id',
                'id_number' => 'nullable|string|max:50',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:male,female,other',
            ]);

            $guest->update($request->all());

            return redirect()->route('guests.index')->with('success', __('Guest updated successfully.'));
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
        if (\Auth::user()->can('guest delete')) {
            $guest = Guest::findOrFail($id);
            
            // Check if guest has active bookings
            if ($guest->bookings()->active()->count() > 0) {
                return redirect()->back()->with('error', __('Cannot delete guest with active bookings.'));
            }
            
            $guest->delete();

            return redirect()->route('guests.index')->with('success', __('Guest deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show guest bookings
     */
    public function bookings($id)
    {
        if (\Auth::user()->can('guest show')) {
            $guest = Guest::findOrFail($id);
            $bookings = $guest->bookings()->with('room')->orderBy('created_at', 'desc')->paginate(10);
            
            return view('booking::guests.bookings', compact('guest', 'bookings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
