<?php

namespace Hubiko\EcommerceHub\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EcommerceCustomerController extends Controller
{
    public function index()
    {
        return view('ecommerce.customers.index');
    }

    public function create()
    {
        return view('ecommerce.customers.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('ecommerce.customers.index');
    }

    public function show($id)
    {
        return view('ecommerce.customers.show', compact('id'));
    }

    public function edit($id)
    {
        return view('ecommerce.customers.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('ecommerce.customers.index');
    }

    public function destroy($id)
    {
        return redirect()->route('ecommerce.customers.index');
    }

    public function orders($id)
    {
        return view('ecommerce.customers.orders', compact('id'));
    }
}
