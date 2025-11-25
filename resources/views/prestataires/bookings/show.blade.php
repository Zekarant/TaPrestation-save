@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">Détails de la réservation #{{ $booking->booking_number }}</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold mb-2">Informations sur le service</h2>
                <p><strong>Service:</strong> {{ $booking->service->title }}</p>
                <p><strong>Description:</strong> {{ $booking->service->description }}</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold mb-2">Informations sur le client</h2>
                <p><strong>Nom:</strong> {{ $booking->client->user->name }}</p>
                <p><strong>Email:</strong> {{ $booking->client->user->email }}</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold mb-2">Détails de la réservation</h2>
                <p><strong>Date de début:</strong> {{ $booking->start_datetime->format('d/m/Y H:i') }}</p>
                <p><strong>Date de fin:</strong> {{ $booking->end_datetime->format('d/m/Y H:i') }}</p>
                <p><strong>Statut:</strong> <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $booking->status === 'confirmed' ? 'green' : ($booking->status === 'pending' ? 'yellow' : 'red') }}-100 text-{{ $booking->status === 'confirmed' ? 'green' : ($booking->status === 'pending' ? 'yellow' : 'red') }}-800">{{ ucfirst($booking->status) }}</span></p>
                <p><strong>Prix total:</strong> {{ number_format($booking->total_price, 2, ',', ' ') }} €</p>
            </div>
        </div>
        <div class="mt-6">
            <a href="{{ route('prestataire.agenda.index') }}" class="text-blue-500 hover:underline">Retour à l'agenda</a>
        </div>
    </div>
</div>
@endsection