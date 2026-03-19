@extends('layouts.admin-modern')

@section('title', 'Paramètres de notifications')
@section('page-title', 'Paramètres de notifications')

@section('content')
<div class="page-header">
    <h1 class="page-title">🔔 Paramètres de notifications</h1>
    <p class="page-subtitle">Configurez les notifications système</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base">
    <form action="{{ route('admin.settings.notifications.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Type de notification</th>
                        <th class="text-center py-3 px-4">Email</th>
                        <th class="text-center py-3 px-4">SMS</th>
                        <th class="text-center py-3 px-4">Push</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $types = [
                            'new_booking' => 'Nouvelle réservation',
                            'booking_confirmed' => 'Réservation confirmée',
                            'booking_cancelled' => 'Réservation annulée',
                            'new_message' => 'Nouveau message',
                            'new_review' => 'Nouvel avis',
                            'payment_received' => 'Paiement reçu',
                            'withdrawal_processed' => 'Retrait traité',
                        ];
                    @endphp
                    @foreach($types as $type => $label)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $label }}</td>
                            <td class="text-center py-3 px-4">
                                <input type="checkbox" name="notifications[{{ $type }}][email]" value="1" 
                                    {{ ($notificationSettings->firstWhere('type', $type)?->email_enabled ?? true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded">
                            </td>
                            <td class="text-center py-3 px-4">
                                <input type="checkbox" name="notifications[{{ $type }}][sms]" value="1"
                                    {{ ($notificationSettings->firstWhere('type', $type)?->sms_enabled ?? false) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded">
                            </td>
                            <td class="text-center py-3 px-4">
                                <input type="checkbox" name="notifications[{{ $type }}][push]" value="1"
                                    {{ ($notificationSettings->firstWhere('type', $type)?->push_enabled ?? true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
