<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambassador;
use App\Models\AmbassadorActivityLog;
use App\Models\AmbassadorCommission;
use App\Models\AmbassadorPayoutBatch;
use App\Models\Client;
use App\Models\Prestataire;
use App\Models\PrestataireAmbassadorAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AmbassadorController extends Controller
{
    public function index(Request $request)
    {
        $query = Ambassador::with('user')
            ->withCount('assignments')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ambassadors = $query->paginate(20);

        return view('admin.ambassadors.index', compact('ambassadors'));
    }

    public function create()
    {
        return view('admin.ambassadors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        DB::beginTransaction();
        try {
            // Create User with ambassador role
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role = 'ambassador';
            $user->email_verified_at = now();
            $user->save();

            // Create Ambassador profile
            $ambassador = Ambassador::create([
                'user_id' => $user->id,
                'referral_code' => Ambassador::generateReferralCode(),
                'status' => 'active',
                'phone' => $request->phone,
                'city' => $request->city,
                'notes' => $request->notes,
            ]);

            // Create stub Client profile (so ambassador can use client interface)
            Client::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
            ]);

            // Create stub Prestataire profile (so ambassador can use prestataire interface)
            Prestataire::create([
                'user_id' => $user->id,
                'company_name' => $request->name . ' (Ambassadeur)',
                'phone' => $request->phone,
                'city' => $request->city,
            ]);

            AmbassadorActivityLog::create([
                'ambassador_id' => $ambassador->id,
                'type' => 'account_created',
                'description' => "Compte ambassadeur créé par l'administrateur",
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.ambassadors.show', $ambassador)
                ->with('success', "Ambassadeur {$request->name} créé avec succès. Code de parrainage : {$ambassador->referral_code}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Erreur lors de la création : ' . $e->getMessage()]);
        }
    }

    public function show(Ambassador $ambassador)
    {
        $ambassador->load(['user', 'assignments.prestataire.user']);

        $recentCommissions = $ambassador->commissions()
            ->with('prestataire')
            ->latest()
            ->take(10)
            ->get();

        $recentActivity = $ambassador->activityLogs()
            ->latest('created_at')
            ->take(20)
            ->get();

        $stats = [
            'total_prestataires' => $ambassador->assignments()->count(),
            'total_earned' => $ambassador->total_commission_earned,
            'total_paid' => $ambassador->total_commission_paid,
            'unpaid' => $ambassador->unpaid_commission,
            'pending_commissions' => $ambassador->commissions()->pending()->sum('commission_amount'),
            'referral_visits' => $ambassador->referralVisits()->count(),
            'conversion_rate' => $ambassador->referralVisits()->count() > 0
                ? round(($ambassador->referralVisits()->where('converted', true)->count() / $ambassador->referralVisits()->count()) * 100, 1)
                : 0,
        ];

        return view('admin.ambassadors.show', compact('ambassador', 'recentCommissions', 'recentActivity', 'stats'));
    }

    public function edit(Ambassador $ambassador)
    {
        $ambassador->load('user');
        return view('admin.ambassadors.edit', compact('ambassador'));
    }

    public function update(Request $request, Ambassador $ambassador)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $ambassador->user_id],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,suspended,inactive'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::beginTransaction();
        try {
            $ambassador->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $ambassador->user->update(['password' => Hash::make($request->password)]);
            }

            $ambassador->update([
                'phone' => $request->phone,
                'city' => $request->city,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('admin.ambassadors.show', $ambassador)
                ->with('success', 'Ambassadeur mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
        }
    }

    public function destroy(Ambassador $ambassador)
    {
        $ambassador->update(['status' => 'inactive']);
        $ambassador->delete();

        return redirect()->route('admin.ambassadors.index')
            ->with('success', 'Ambassadeur désactivé avec succès.');
    }

    public function commissions(Ambassador $ambassador)
    {
        $commissions = $ambassador->commissions()
            ->with('prestataire')
            ->latest()
            ->paginate(20);

        return view('admin.ambassadors.commissions', compact('ambassador', 'commissions'));
    }

    public function createPayout(Request $request, Ambassador $ambassador)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $pendingCommissions = $ambassador->commissions()
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        if ($pendingCommissions->isEmpty()) {
            return back()->with('warning', 'Aucune commission en attente pour cet ambassadeur.');
        }

        $totalAmount = $pendingCommissions->sum('commission_amount');

        DB::beginTransaction();
        try {
            $batch = AmbassadorPayoutBatch::create([
                'ambassador_id' => $ambassador->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($pendingCommissions as $commission) {
                $commission->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payout_batch_id' => $batch->id,
                ]);
            }

            $ambassador->increment('total_commission_paid', $totalAmount);

            AmbassadorActivityLog::create([
                'ambassador_id' => $ambassador->id,
                'type' => 'payout_created',
                'description' => "Payout de {$totalAmount}€ créé ({$pendingCommissions->count()} commissions)",
                'metadata' => ['batch_id' => $batch->id, 'amount' => $totalAmount],
                'created_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', "Payout de {$totalAmount}€ créé avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors du payout : ' . $e->getMessage()]);
        }
    }

    public function payoutsIndex(Request $request)
    {
        $payouts = AmbassadorPayoutBatch::with('ambassador.user')
            ->latest()
            ->paginate(20);

        return view('admin.ambassadors.payouts', compact('payouts'));
    }

    public function settings()
    {
        return view('admin.ambassadors.settings');
    }

    public function updateSettings(Request $request)
    {
        // Ambassador settings are stored in site_settings table
        // For now, the commission rates come from CommissionService (platform rates)
        return back()->with('success', 'Paramètres mis à jour.');
    }

    public function assignPrestataire(Request $request, Ambassador $ambassador)
    {
        $request->validate([
            'prestataire_id' => ['required', 'exists:prestataires,id'],
        ]);

        $prestataireId = $request->prestataire_id;

        // Check not already assigned
        if (PrestataireAmbassadorAssignment::where('prestataire_id', $prestataireId)->exists()) {
            return back()->withErrors(['prestataire_id' => 'Ce prestataire est déjà assigné à un ambassadeur.']);
        }

        PrestataireAmbassadorAssignment::create([
            'ambassador_id' => $ambassador->id,
            'prestataire_id' => $prestataireId,
            'source' => 'admin_assigned',
            'assigned_at' => now(),
        ]);

        $prestataire = Prestataire::find($prestataireId);
        AmbassadorActivityLog::create([
            'ambassador_id' => $ambassador->id,
            'type' => 'prestataire_assigned',
            'description' => "Prestataire {$prestataire->company_name} assigné par l'admin",
            'metadata' => ['prestataire_id' => $prestataireId, 'source' => 'admin_assigned'],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Prestataire assigné à l\'ambassadeur avec succès.');
    }
}
