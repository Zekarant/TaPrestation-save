<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:client']);
    }

    /**
     * Affiche la liste des projets du client (redirection vers les demandes).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Rediriger vers le tableau de bord client
        return redirect()->route('client.dashboard');
    }
}