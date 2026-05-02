<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ToolsController extends Controller
{
    public function index()
    {
        $balance = auth()->user()->getActiveAccount()->balance;
        return view('tools.index', compact('balance'));
    }
}
