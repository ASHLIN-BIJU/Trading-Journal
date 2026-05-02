<?php

namespace App\Http\Controllers;

use App\Http\Requests\TradeRequest;
use App\Models\Tag;
use App\Models\Trade;
use App\Services\TradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TradeController extends Controller
{
    public function __construct(private TradeCalculationService $calculator) {}

    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = $user->getActiveAccount()->trades()->with(['tags', 'images'])->orderByDesc('trade_date');

        // Filters
        if ($request->filled('asset')) {
            $query->byAsset($request->asset);
        }
        if ($request->filled('result')) {
            $query->byResult($request->result);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from') || $request->filled('to')) {
            $query->dateRange($request->from, $request->to);
        }
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn($q) => $q->where('tags.id', $request->tag));
        }

        $trades    = $query->paginate(20)->withQueryString();
        $tags      = $user->tags()->orderBy('name')->get();
        $assets    = $user->getActiveAccount()->trades()->select('asset')->distinct()->pluck('asset');

        return view('trades.index', compact('trades', 'tags', 'assets'));
    }

    public function create()
    {
        $tags = Auth::user()->tags()->orderBy('name')->get();
        return view('trades.create', compact('tags'));
    }

    public function store(TradeRequest $request)
    {
        $data      = $request->validated();
        $calc      = $this->calculator->calculate($data);
        $user      = Auth::user();

        // Determine status
        $status = isset($data['exit_price']) && $data['exit_price'] ? 'closed' : 'open';

        $trade = $user->getActiveAccount()->trades()->create(array_merge($data, $calc, [
            'user_id' => $user->id,
            'status'    => $status,
            'closed_at' => $status === 'closed' ? now() : null,
        ]));

        // Handle tags
        if (!empty($data['tag_ids'])) {
            $trade->tags()->sync($data['tag_ids']);
        }

        // Handle image uploads
        $this->handleImages($request, $trade);

        // Refresh analytics cache
        app(\App\Services\AnalyticsService::class)->refreshCache($user->getActiveAccount());

        return redirect()->route('trades.show', $trade)
            ->with('success', 'Trade logged successfully!');
    }

    public function show(Trade $trade)
    {
        Gate::authorize('view', $trade);
        $trade->load(['tags', 'images']);
        return view('trades.show', compact('trade'));
    }

    public function edit(Trade $trade)
    {
        Gate::authorize('update', $trade);
        $tags        = Auth::user()->tags()->orderBy('name')->get();
        $selectedTags = $trade->tags->pluck('id')->toArray();
        $trade->load('images');
        return view('trades.edit', compact('trade', 'tags', 'selectedTags'));
    }

    public function update(TradeRequest $request, Trade $trade)
    {
        Gate::authorize('update', $trade);

        $data  = $request->validated();
        $calc  = $this->calculator->calculate($data);
        $status = isset($data['exit_price']) && $data['exit_price'] ? 'closed' : 'open';

        $trade->update(array_merge($data, $calc, [
            'status'    => $status,
            'closed_at' => $status === 'closed' ? ($trade->closed_at ?? now()) : null,
        ]));

        if (isset($data['tag_ids'])) {
            $trade->tags()->sync($data['tag_ids']);
        }

        $this->handleImages($request, $trade);

        // Refresh analytics cache
        app(\App\Services\AnalyticsService::class)->refreshCache(Auth::user()->getActiveAccount());

        return redirect()->route('trades.show', $trade)
            ->with('success', 'Trade updated successfully!');
    }

    public function destroy(Trade $trade)
    {
        Gate::authorize('delete', $trade);

        // Remove images from storage
        foreach ($trade->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $trade->delete();

        // Refresh analytics cache
        app(\App\Services\AnalyticsService::class)->refreshCache(Auth::user()->getActiveAccount());

        return redirect()->route('trades.index')
            ->with('success', 'Trade deleted.');
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function handleImages(Request $request, Trade $trade): void
    {
        foreach (['entry_screenshot', 'exit_screenshot'] as $field) {
            if ($request->hasFile($field) && $request->file($field)->isValid()) {
                $type = str_replace('_screenshot', '', $field);
                $path = $request->file($field)->store('trade-images', 'public');
                $trade->images()->create([
                    'image_path' => $path,
                    'image_type' => $type,
                ]);
            }
        }
    }
}
