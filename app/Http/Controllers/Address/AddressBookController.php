<?php

namespace App\Http\Controllers\Address;

use App\Http\Controllers\Controller;
use App\Models\AddressBook;
use Illuminate\Http\Request;

use App\Support\TableExistenceCache;
class AddressBookController extends Controller
{
    /**
     * Show all addresses
     */
    public function index()
    {
        if (!TableExistenceCache::has('address_books')) {
            return view('address-book.index', [
                'addresses' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $addresses = auth()->user()->addresses()->latest()->limit(50)->get();
            return view('address-book.index', compact('addresses'));
        } catch (\Exception $e) {
            return view('address-book.index', [
                'addresses' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Store a new address
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'tags' => 'nullable|array',
            'is_default' => 'boolean',
        ]);

        // If setting as default, unset others
        if ($validated['is_default'] ?? false) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        $address = auth()->user()->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'address' => $address,
            'message' => 'Adresse ajoutée avec succès.',
        ]);
    }

    /**
     * Update an address
     */
    public function update(Request $request, AddressBook $address)
    {
        $this->authorize('update', $address);

        $validated = $request->validate([
            'label' => 'string|max:50',
            'recipient_name' => 'string|max:100',
            'street' => 'string|max:255',
            'city' => 'string|max:100',
            'postal_code' => 'string|max:10',
            'country' => 'string|max:100',
            'phone' => 'string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'tags' => 'nullable|array',
        ]);

        $address->update($validated);

        return response()->json([
            'success' => true,
            'address' => $address,
            'message' => 'Adresse mise à jour avec succès.',
        ]);
    }

    /**
     * Delete an address
     */
    public function destroy(AddressBook $address)
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Adresse supprimée avec succès.',
        ]);
    }

    /**
     * Set as default address
     */
    public function setDefault(AddressBook $address)
    {
        $this->authorize('update', $address);

        // Unset other defaults
        auth()->user()->addresses()->update(['is_default' => false]);

        // Set this as default
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated',
        ]);
    }

    // ============================================================================
    // CLIENT METHODS
    // ============================================================================

    /**
     * Client address book index
     */
    public function clientIndex()
    {
        if (!TableExistenceCache::has('address_books')) {
            return view('client.address-book.index', [
                'addresses' => collect(),
                'defaultAddress' => null,
                'tableNotExists' => true,
            ]);
        }

        try {
            $addresses = auth()->user()->addresses()->get();
            $defaultAddress = auth()->user()->addresses()->where('is_default', true)->first();

            return view('client.address-book.index', compact('addresses', 'defaultAddress'));
        } catch (\Exception $e) {
            return view('client.address-book.index', [
                'addresses' => collect(),
                'defaultAddress' => null,
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // PRESTATAIRE METHODS
    // ============================================================================

    /**
     * Prestataire address book index
     */
    public function prestataireIndex()
    {
        if (!TableExistenceCache::has('address_books')) {
            return view('prestataire.address-book.index', [
                'addresses' => collect(),
                'defaultAddress' => null,
                'frequentDeliveryAddresses' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $addresses = auth()->user()->addresses()->get();
            $defaultAddress = auth()->user()->addresses()->where('is_default', true)->first();

            // Pour les prestataires, on peut aussi afficher les adresses de livraison fréquentes
            $frequentDeliveryAddresses = collect();
            if (TableExistenceCache::has('delivery_orders')) {
                try {
                    $frequentDeliveryAddresses = \App\Models\DeliveryOrder::whereHas('booking.service', function ($q) {
                        $q->where('prestataire_id', auth()->user()->prestataire?->id);
                    })->selectRaw('recipient_address, COUNT(*) as count')
                        ->groupBy('recipient_address')
                        ->orderByDesc('count')
                        ->limit(5)
                        ->get();
                } catch (\Exception $e) {
                    // Table not available
                }
            }

            return view('prestataire.address-book.index', compact('addresses', 'defaultAddress', 'frequentDeliveryAddresses'));
        } catch (\Exception $e) {
            return view('prestataire.address-book.index', [
                'addresses' => collect(),
                'defaultAddress' => null,
                'frequentDeliveryAddresses' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // ADMIN METHODS
    // ============================================================================

    /**
     * Admin all addresses
     */
    public function adminAllAddresses()
    {
        if (!TableExistenceCache::has('address_books')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                30,
                1,
                ['path' => request()->url()]
            );
            return view('admin.address-book.index', [
                'addresses' => $emptyPaginator,
                'stats' => ['total_addresses' => 0, 'users_with_addresses' => 0, 'default_addresses' => 0],
                'tableNotExists' => true,
            ]);
        }

        try {
            $addresses = AddressBook::with('user')
                ->latest()
                ->paginate(30);

            $stats = [
                'total_addresses' => AddressBook::count(),
                'users_with_addresses' => AddressBook::distinct('user_id')->count('user_id'),
                'default_addresses' => AddressBook::where('is_default', true)->count(),
            ];

            return view('admin.address-book.index', compact('addresses', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                30,
                1,
                ['path' => request()->url()]
            );
            return view('admin.address-book.index', [
                'addresses' => $emptyPaginator,
                'stats' => ['total_addresses' => 0, 'users_with_addresses' => 0, 'default_addresses' => 0],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Admin user addresses
     */
    public function adminUserAddresses(\App\Models\User $user)
    {
        if (!TableExistenceCache::has('address_books')) {
            return view('admin.address-book.user', [
                'user' => $user,
                'addresses' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $addresses = $user->addresses()->get();
            return view('admin.address-book.user', compact('user', 'addresses'));
        } catch (\Exception $e) {
            return view('admin.address-book.user', [
                'user' => $user,
                'addresses' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }
}
