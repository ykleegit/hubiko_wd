<?php

namespace Hubiko\EcommerceHub\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function process(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function webhook(Request $request, $gateway)
    {
        return response()->json(['success' => true]);
    }

    public function success()
    {
        return view('ecommerce.payment.success');
    }

    public function cancel()
    {
        return view('ecommerce.payment.cancel');
    }
}
