<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class QrCodeController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $url = route('prestataires.show', ['prestataire' => $user->prestataire]);

        return view('prestataire.qrcode', compact('url'));
    }


}