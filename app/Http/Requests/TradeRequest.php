<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset'            => 'required|string|max:20',
            'type'             => 'required|in:buy,sell',
            'entry_price'      => 'required|numeric|min:0',
            'exit_price'       => 'nullable|numeric|min:0',
            'stop_loss'        => 'nullable|numeric|min:0',
            'take_profit'      => 'nullable|numeric|min:0',
            'lot_size'         => 'required|numeric|min:0.001',
            'notes'            => 'nullable|string|max:5000',
            'trade_date'       => 'required|date',
            'session'          => 'nullable|string|in:London,New York,Tokyo,Sydney,Other',
            'timeframe'        => 'nullable|string|in:1M,5M,15M,30M,1H,4H,1D,1W',
            'tag_ids'          => 'nullable|array',
            'tag_ids.*'        => 'integer|exists:tags,id',
            'entry_screenshot' => 'nullable|image|max:5120',
            'exit_screenshot'  => 'nullable|image|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'asset.required'       => 'Please enter the trading asset (e.g. XAUUSD).',
            'entry_price.required' => 'Entry price is required.',
            'lot_size.required'    => 'Lot size is required.',
            'lot_size.min'         => 'Lot size must be at least 0.001.',
            'trade_date.required'  => 'Trade date is required.',
        ];
    }
}
