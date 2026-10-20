<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Placeholder until the Reports module is rebuilt (see rebuild plan).
 */
class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }
}
