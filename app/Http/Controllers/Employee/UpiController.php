<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UpiController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'upi_id' => ['required', 'string', 'max:100', 'regex:'.User::UPI_REGEX],
        ], [
            'upi_id.regex' => 'Please enter a valid UPI ID, for example name@okaxis.',
        ]);

        $request->user()->update(['upi_id' => strtolower(trim($data['upi_id']))]);

        return back()->with('success', 'UPI ID saved successfully.');
    }
}
