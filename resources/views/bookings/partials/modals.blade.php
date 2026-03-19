<!-- Cancel Modal -->
<div id="cancelModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeCancelModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto pointer-events-none">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0 pointer-events-auto">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">Annuler la réservation</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Êtes-vous sûr de vouloir annuler cette réservation ? Cette action est irréversible.
                                </p>
                                <form action="{{ route('bookings.cancel', $booking) }}" method="POST" class="mt-4">
                                    @csrf
                                    @method('PUT')
                                    
                                    @php
                                        $sessionId = null;
                                        if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
                                            $sessionId = $matches[1];
                                        }
                                        $hasRelatedBookings = isset($isMultiSlotSession) && $isMultiSlotSession && isset($allBookings) && $allBookings->count() > 1;
                                    @endphp
                                    
                                    @if($hasRelatedBookings)
                                        <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                            <p class="text-sm text-blue-800 font-medium mb-3">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Cette réservation fait partie d'une session de {{ $allBookings->count() }} créneaux.
                                            </p>
                                            <div class="space-y-2">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio" name="cancel_single" value="1" class="mr-2 text-blue-600 focus:ring-blue-500" checked>
                                                    <span class="text-sm text-gray-700">Annuler uniquement ce créneau (#{{ $booking->booking_number }})</span>
                                                </label>
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio" name="cancel_single" value="0" class="mr-2 text-red-600 focus:ring-red-500">
                                                    <span class="text-sm text-gray-700">Annuler tous les créneaux de la session ({{ $allBookings->whereIn('status', ['pending', 'confirmed'])->count() }} créneaux)</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-4">
                                        <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-1">Raison (optionnel)</label>
                                        <textarea name="cancellation_reason" id="cancellation_reason" rows="3" 
                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                  placeholder="Expliquez pourquoi..."></textarea>
                                    </div>
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                            Confirmer l'annulation
                                        </button>
                                        <button type="button" onclick="closeCancelModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Refuse Modal -->
<div id="refuseModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeRefuseModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto pointer-events-none">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0 pointer-events-auto">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-ban text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">Refuser la réservation</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Êtes-vous sûr de vouloir refuser cette réservation ?
                                </p>
                                <form action="{{ route('bookings.refuse', $booking) }}" method="POST" class="mt-4">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="refusal_reason" class="block text-sm font-medium text-gray-700 mb-1">Raison du refus (optionnel)</label>
                                        <textarea name="refusal_reason" id="refusal_reason" rows="3" 
                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                  placeholder="Expliquez pourquoi..."></textarea>
                                    </div>
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                            Confirmer le refus
                                        </button>
                                        <button type="button" onclick="closeRefuseModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}

function openRefuseModal() {
    document.getElementById('refuseModal').classList.remove('hidden');
}

function closeRefuseModal() {
    document.getElementById('refuseModal').classList.add('hidden');
}
</script>
