<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Ambassador;
use App\Models\AmbassadorActivityLog;
use App\Models\Category;
use App\Models\Client;
use App\Models\Prestataire;
use App\Models\PrestataireAmbassadorAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PrestataireController extends Controller
{
    public function index(Request $request)
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        $prestataires = $ambassador->assignments()
            ->with(['prestataire.user', 'prestataire.services'])
            ->latest('assigned_at')
            ->paginate(20);

        return view('ambassador.prestataires.index', compact('ambassador', 'prestataires'));
    }

    public function create()
    {
        $categories = Category::ofTypeService()->whereNull('parent_id')->orderBy('name')->get();
        return view('ambassador.prestataires.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'company_name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'company_name.required' => 'Le nom de l\'enseigne est obligatoire.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'city.required' => 'La ville est obligatoire.',
            'category_id.required' => 'La catégorie est obligatoire.',
            'subcategory_id.required' => 'La sous-catégorie est obligatoire.',
        ]);

        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        DB::beginTransaction();
        try {
            $password = Str::random(10);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($password);
            $user->role = 'prestataire';
            $user->email_verified_at = now();
            $user->save();

            $category = Category::find($request->category_id);
            $subcategory = Category::find($request->subcategory_id);

            $prestataire = Prestataire::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'phone' => $request->phone,
                'city' => $request->city,
                'secteur_activite' => $category?->name,
                'competences' => $subcategory?->name,
                'description' => $request->description,
            ]);

            // Create client profile for dual usage
            Client::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
            ]);

            PrestataireAmbassadorAssignment::create([
                'ambassador_id' => $ambassador->id,
                'prestataire_id' => $prestataire->id,
                'source' => 'manual_creation',
                'assigned_at' => now(),
            ]);

            AmbassadorActivityLog::create([
                'ambassador_id' => $ambassador->id,
                'type' => 'prestataire_created',
                'description' => "Prestataire {$request->company_name} créé manuellement",
                'metadata' => ['prestataire_id' => $prestataire->id],
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('ambassador.prestataires.index')
                ->with('success', "Prestataire {$request->company_name} créé. Mot de passe temporaire : {$password} (communiquez-le au prestataire)");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Erreur : ' . $e->getMessage()]);
        }
    }

    public function show(Prestataire $prestataire)
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        // Ensure this prestataire belongs to this ambassador
        $assignment = PrestataireAmbassadorAssignment::where('ambassador_id', $ambassador->id)
            ->where('prestataire_id', $prestataire->id)
            ->firstOrFail();

        $prestataire->load(['user', 'services', 'bookings']);

        $commissions = $ambassador->commissions()
            ->where('prestataire_id', $prestataire->id)
            ->latest()
            ->take(20)
            ->get();

        return view('ambassador.prestataires.show', compact('ambassador', 'prestataire', 'assignment', 'commissions'));
    }
}
