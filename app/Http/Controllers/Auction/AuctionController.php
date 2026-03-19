<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\AuctionBid;
use App\Services\AuctionService;
use Illuminate\Http\Request;

use App\Support\TableExistenceCache;
class AuctionController extends Controller
{
    protected $auctionService;

    public function __construct(AuctionService $auctionService)
    {
        $this->auctionService = $auctionService;
    }

    /**
     * Show auction bids for a service
     */
    public function showBids(Service $service)
    {
        $this->authorize('view', $service);

        $bids = $this->auctionService->getActiveBids($service);
        $highestBid = $this->auctionService->getHighestBid($service);

        return view('auctions.bids', [
            'service' => $service,
            'bids' => $bids,
            'highestBid' => $highestBid,
        ]);
    }

    /**
     * Place an auction bid
     */
    public function placeBid(Request $request, Service $service)
    {
        $validated = $request->validate([
            'bid_amount' => 'required|numeric|min:0.01',
            'message' => 'nullable|string|max:500',
            'days_to_expire' => 'integer|min:1|max:30',
        ]);

        try {
            $bid = $this->auctionService->createBid(
                $service,
                auth()->user()->client,
                $validated['bid_amount'],
                $validated['message'] ?? null,
                $validated['days_to_expire'] ?? 7
            );

            return response()->json([
                'success' => true,
                'message' => 'Offre placée avec succès.',
                'bid_id' => $bid->id,
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'Impossible d\'enregistrer l\'offre pour le moment.',
            ], 422);
        }
    }

    /**
     * Accept a bid (for prestataire)
     */
    public function acceptBid(AuctionBid $bid)
    {
        $this->authorize('acceptBid', $bid);

        try {
            $this->auctionService->acceptBid($bid);

            return response()->json([
                'success' => true,
                'message' => 'Bid accepted and booking created',
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'Impossible d\'accepter cette offre pour le moment.',
            ], 422);
        }
    }

    /**
     * Reject a bid (for prestataire)
     */
    public function rejectBid(AuctionBid $bid)
    {
        $this->authorize('rejectBid', $bid);

        try {
            $this->auctionService->rejectBid($bid);

            return response()->json([
                'success' => true,
                'message' => 'Bid rejected',
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'Impossible de refuser cette offre pour le moment.',
            ], 422);
        }
    }

    /**
     * Get auction statistics
     */
    public function stats()
    {
        $stats = $this->auctionService->getAuctionStats(auth()->user()->prestataire->id);

        return view('auctions.stats', compact('stats'));
    }

    /**
     * Get my bids (for clients)
     */
    public function myBids()
    {
        // Vérifier si la table auction_bids existe
        if (!TableExistenceCache::has('auction_bids')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('auctions.my-bids', [
                'bids' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }

        try {
            $bids = AuctionBid::where('client_id', auth()->user()->client->id)
                ->latest()
                ->paginate(20);

            return view('auctions.my-bids', compact('bids'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('auctions.my-bids', [
                'bids' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // CLIENT METHODS
    // ============================================================================

    /**
     * Client auctions index
     */
    public function clientIndex()
    {
        $user = auth()->user();
        $client = $user->client;

        // Vérifier si la table auction_bids existe
        if (!TableExistenceCache::has('auction_bids')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('client.auctions.index', [
                'myBids' => $emptyPaginator,
                'availableAuctions' => $emptyPaginator,
                'stats' => ['active_bids' => 0, 'won_auctions' => 0, 'total_bid_amount' => 0],
                'tableNotExists' => true,
            ]);
        }

        try {
            $myBids = AuctionBid::where('client_id', $client?->id)
                ->with(['service.prestataire'])
                ->latest()
                ->paginate(15);

            $availableAuctions = Service::where('auction_enabled', true)
                ->where('auction_end_date', '>', now())
                ->with(['prestataire', 'category'])
                ->paginate(15);

            $stats = [
                'active_bids' => AuctionBid::where('client_id', $client?->id)->where('status', 'active')->count(),
                'won_auctions' => AuctionBid::where('client_id', $client?->id)->where('status', 'won')->count(),
                'total_bid_amount' => AuctionBid::where('client_id', $client?->id)->where('status', 'active')->sum('bid_amount'),
            ];

            return view('client.auctions.index', compact('myBids', 'availableAuctions', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('client.auctions.index', [
                'myBids' => $emptyPaginator,
                'availableAuctions' => $emptyPaginator,
                'stats' => ['active_bids' => 0, 'won_auctions' => 0, 'total_bid_amount' => 0],
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // PRESTATAIRE METHODS
    // ============================================================================

    /**
     * Prestataire auctions index
     */
    public function prestataireIndex()
    {
        $user = auth()->user();
        $prestataire = $user->prestataire;

        // Vérifier si la table auction_bids existe
        if (!TableExistenceCache::has('auction_bids')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('prestataire.auctions.index', [
                'myServices' => $emptyPaginator,
                'pendingBids' => collect(),
                'stats' => ['total_bids_received' => 0, 'pending_bids' => 0, 'accepted_bids' => 0],
                'tableNotExists' => true,
            ]);
        }

        try {
            $myServices = Service::where('prestataire_id', $prestataire->id)
                ->where('auction_enabled', true)
                ->with(['bids' => function($q) {
                    $q->latest()->limit(5);
                }])
                ->paginate(15);

            $pendingBids = AuctionBid::whereHas('service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })->where('status', 'pending')->with(['client', 'service'])->get();

            $stats = [
                'total_bids_received' => AuctionBid::whereHas('service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->count(),
                'pending_bids' => $pendingBids->count(),
                'accepted_bids' => AuctionBid::whereHas('service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->where('status', 'accepted')->count(),
            ];

            return view('prestataire.auctions.index', compact('myServices', 'pendingBids', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('prestataire.auctions.index', [
                'myServices' => $emptyPaginator,
                'pendingBids' => collect(),
                'stats' => ['total_bids_received' => 0, 'pending_bids' => 0, 'accepted_bids' => 0],
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // ADMIN METHODS
    // ============================================================================

    /**
     * Admin all bids
     */
    public function adminAllBids()
    {
        // Vérifier si la table auction_bids existe
        if (!TableExistenceCache::has('auction_bids')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 30, 1, ['path' => request()->url()]
            );
            return view('admin.auctions.index', [
                'bids' => $emptyPaginator,
                'stats' => ['total_bids' => 0, 'active_auctions' => 0, 'completed_auctions' => 0, 'total_value' => 0],
                'tableNotExists' => true,
            ]);
        }

        try {
            $bids = AuctionBid::with(['client', 'service.prestataire'])
                ->latest()
                ->paginate(30);

            $stats = [
                'total_bids' => AuctionBid::count(),
                'active_auctions' => Service::where('auction_enabled', true)
                    ->where('auction_end_date', '>', now())->count(),
                'completed_auctions' => AuctionBid::where('status', 'won')->count(),
                'total_value' => AuctionBid::where('status', 'won')->sum('bid_amount'),
            ];

            return view('admin.auctions.index', compact('bids', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 30, 1, ['path' => request()->url()]
            );
            return view('admin.auctions.index', [
                'bids' => $emptyPaginator,
                'stats' => ['total_bids' => 0, 'active_auctions' => 0, 'completed_auctions' => 0, 'total_value' => 0],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Admin disputes
     */
    public function adminDisputes()
    {
        // Vérifier si la table auction_bids existe
        if (!TableExistenceCache::has('auction_bids')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('admin.auctions.disputes', [
                'disputes' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }

        try {
            $disputes = AuctionBid::where('status', 'disputed')
                ->with(['client', 'service.prestataire'])
                ->latest()
                ->paginate(20);

            return view('admin.auctions.disputes', compact('disputes'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('admin.auctions.disputes', [
                'disputes' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Resolve dispute
     */
    public function resolveDispute(Request $request, AuctionBid $bid)
    {
        $validated = $request->validate([
            'resolution' => 'required|in:favor_client,favor_prestataire,refund',
            'notes' => 'nullable|string|max:500',
        ]);

        $bid->update([
            'status' => 'resolved',
            'resolution' => $validated['resolution'],
            'resolution_notes' => $validated['notes'],
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Litige résolu avec succès');
    }

    /**
     * Admin analytics
     */
    public function adminAnalytics()
    {
        // Vérifier si la table auction_bids existe
        if (!TableExistenceCache::has('auction_bids')) {
            return view('admin.auctions.analytics', [
                'monthlyStats' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $monthlyStats = AuctionBid::selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(bid_amount) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get();

            return view('admin.auctions.analytics', compact('monthlyStats'));
        } catch (\Exception $e) {
            return view('admin.auctions.analytics', [
                'monthlyStats' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }
}
