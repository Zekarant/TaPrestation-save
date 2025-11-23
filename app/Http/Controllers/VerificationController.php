<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ClientVerificationRequest;
use App\Models\Prestataire;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'document' => 'nullable|file|mimes:pdf,jpg,png',
        ]);

        $prestataire = auth()->user()->prestataire;

        if ($prestataire->verificationRequest()->exists()) {
            return response()->json(['message' => 'You already have a pending verification request.'], 409);
        }

        $path = null;
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('verifications', 'public');
        }

        $verificationRequest = ClientVerificationRequest::create([
            'prestataire_id' => $prestataire->id,
            'document' => $path,
        ]);

        return response()->json($verificationRequest, 201);
    }

    public function update(Request $request, ClientVerificationRequest $verificationRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_comment' => 'nullable|string',
        ]);

        $verificationRequest->update($request->only('status', 'admin_comment'));

        if ($request->status === 'approved') {
            $verificationRequest->prestataire->update(['is_verified' => true]);
        }

        return response()->json($verificationRequest);
    }
}
