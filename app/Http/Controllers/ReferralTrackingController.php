<?php

namespace App\Http\Controllers;

use App\Models\Ambassador;
use App\Models\AmbassadorReferralVisit;
use Illuminate\Http\Request;

class ReferralTrackingController extends Controller
{
    public function track(Request $request, string $code)
    {
        $ambassador = Ambassador::where('referral_code', $code)
            ->where('status', 'active')
            ->first();

        if (!$ambassador) {
            return redirect()->route('register');
        }

        // Store in session for capture at registration
        session(['ambassador_referral_code' => $code]);

        // Log the visit
        AmbassadorReferralVisit::create([
            'ambassador_id' => $ambassador->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'referer_url' => substr((string) $request->headers->get('referer'), 0, 500),
            'converted' => false,
            'visited_at' => now(),
        ]);

        return redirect()->route('register');
    }
}
