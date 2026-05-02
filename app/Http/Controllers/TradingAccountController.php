<?php

namespace App\Http\Controllers;

use App\Models\TradingAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradingAccountController extends Controller
{
    public function index()
    {
        $accounts = Auth::user()->accounts()->orderBy('name')->get();
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'initial_capital' => 'required|numeric|min:0',
        ]);

        Auth::user()->accounts()->create([
            'name' => $request->name,
            'initial_capital' => $request->initial_capital,
            'balance' => $request->initial_capital,
            'status' => 'active',
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit(TradingAccount $account)
    {
        if ($account->user_id !== Auth::id()) abort(403);
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, TradingAccount $account)
    {
        if ($account->user_id !== Auth::id()) abort(403);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'initial_capital' => 'required|numeric|min:0',
            'status' => 'required|in:active,blown,passed',
        ]);

        $account->update($request->only('name', 'initial_capital', 'status'));

        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(TradingAccount $account)
    {
        if ($account->user_id !== Auth::id()) abort(403);
        
        // Prevent deleting active account if it's the only one
        if (Auth::user()->accounts()->count() <= 1) {
            return back()->withErrors(['account' => 'Cannot delete your only account.']);
        }

        $account->delete();
        
        // If deleted account was active, switch to first available
        if (Auth::user()->active_account_id == $account->id) {
            Auth::user()->update(['active_account_id' => Auth::user()->accounts()->first()->id]);
        }

        return redirect()->route('accounts.index')->with('success', 'Account deleted.');
    }

    public function switch(TradingAccount $account)
    {
        if ($account->user_id !== Auth::id()) abort(403);
        
        Auth::user()->update(['active_account_id' => $account->id]);
        
        return redirect()->back()->with('success', 'Switched to ' . $account->name);
    }
}
