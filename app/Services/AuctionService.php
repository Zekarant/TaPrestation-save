<?php

namespace App\Services;

use App\Models\AuctionBid;
use App\Models\Service;
use App\Models\Client;
use Carbon\Carbon;

class AuctionService
{
    /**
     * Create a new auction bid
     */
    public function createBid(Service $service, Client $client, float $bidAmount, string $message = null, int $daysToExpire = 7): AuctionBid
    {
        $bid = AuctionBid::create([
            'service_id' => $service->id,
            'client_id' => $client->id,
            'bid_amount' => $bidAmount,
            'currency' => 'EUR',
            'status' => 'pending',
            'message' => $message,
            'expires_at' => now()->addDays($daysToExpire),
        ]);

        // Notify prestataire
        $service->prestataire->notify(new \App\Notifications\NewAuctionBidNotification($bid));

        return $bid;
    }

    /**
     * Accept an auction bid
     */
    public function acceptBid(AuctionBid $bid): void
    {
        $bid->accept();

        // Create booking from accepted bid
        $booking = \App\Models\Booking::create([
            'client_id' => $bid->client_id,
            'service_id' => $bid->service_id,
            'prestataire_id' => $bid->service->prestataire_id,
            'agreed_price' => $bid->bid_amount,
            'status' => 'accepted',
            'notes' => $bid->message,
        ]);

        // Notify client
        $bid->client->notify(new \App\Notifications\AuctionBidAcceptedNotification($booking));
    }

    /**
     * Reject an auction bid
     */
    public function rejectBid(AuctionBid $bid): void
    {
        $bid->reject();

        // Notify client
        $bid->client->notify(new \App\Notifications\AuctionBidRejectedNotification($bid));
    }

    /**
     * Get active bids for a service
     */
    public function getActiveBids(Service $service)
    {
        return $service->bids()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderByDesc('bid_amount')
            ->get();
    }

    /**
     * Get highest bid for a service
     */
    public function getHighestBid(Service $service): ?AuctionBid
    {
        return $service->bids()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderByDesc('bid_amount')
            ->first();
    }

    /**
     * Expire old bids (run via scheduler)
     */
    public function expireOldBids(): int
    {
        return AuctionBid::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get auction statistics for a prestataire
     */
    public function getAuctionStats($prestataireId): array
    {
        $services = Service::where('prestataire_id', $prestataireId)->pluck('id');

        return [
            'total_bids' => AuctionBid::whereIn('service_id', $services)->count(),
            'pending_bids' => AuctionBid::whereIn('service_id', $services)->where('status', 'pending')->count(),
            'accepted_bids' => AuctionBid::whereIn('service_id', $services)->where('status', 'accepted')->count(),
            'rejected_bids' => AuctionBid::whereIn('service_id', $services)->where('status', 'rejected')->count(),
            'average_bid' => AuctionBid::whereIn('service_id', $services)->avg('bid_amount'),
            'highest_bid' => AuctionBid::whereIn('service_id', $services)->max('bid_amount'),
        ];
    }
}
