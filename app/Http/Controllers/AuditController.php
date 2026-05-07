<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function show(): View
    {
        return view('audit.run');
    }

    public function run(Request $request): RedirectResponse
    {
        $date = $request->input('date', today()->toDateString());

        \Artisan::call('audit:night', ['--date' => $date]);
        $output = \Artisan::output();

        return back()->with('audit_output', $output)->with('success', 'Ночной аудит выполнен.');
    }
}
