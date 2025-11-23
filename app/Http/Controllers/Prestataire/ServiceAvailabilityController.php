<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceAvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Service $service)
    {
        $this->authorize('update', $service);

        $availabilities = $service->availabilities()->paginate(10);

        return view('prestataire.services.availabilities.index', compact('service', 'availabilities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Service $service)
    {
        $this->authorize('update', $service);

        return view('prestataire.services.availabilities.create', compact('service'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $service->availabilities()->create($request->all());

        return redirect()->route('prestataire.services.availabilities.index', $service)
            ->with('success', 'Disponibilité ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Availability $availability)
    {
        $this->authorize('update', $availability->service);

        return view('prestataire.services.availabilities.edit', compact('availability'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Availability $availability)
    {
        $this->authorize('update', $availability->service);

        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $availability->update($request->all());

        return redirect()->route('prestataire.services.availabilities.index', $availability->service)
            ->with('success', 'Disponibilité mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Availability $availability)
    {
        $this->authorize('update', $availability->service);

        $availability->delete();

        return redirect()->route('prestataire.services.availabilities.index', $availability->service)
            ->with('success', 'Disponibilité supprimée avec succès.');
    }
}
