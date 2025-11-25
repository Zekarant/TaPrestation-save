<!-- Cancel Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border border-blue-200 w-96 shadow-xl rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-3">Annuler la réservation</h3>
            <div class="mt-3 px-7 py-4">
                <p class="text-gray-600">
                    Êtes-vous sûr de vouloir annuler cette réservation ? Cette action est irréversible.
                </p>
            </div>
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST" class="mt-5">
                @csrf
                @method('PUT')
                <div class="mb-5">
                    <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2 text-left">Raison de l'annulation (optionnel)</label>
                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3" 
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                              placeholder="Expliquez pourquoi vous annulez cette réservation..."></textarea>
                </div>
                <div class="flex justify-center gap-3">
                    <button type="button" onclick="closeCancelModal()" 
                            class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition duration-200">
                        Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Refuse Modal -->
<div id="refuseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border border-blue-200 w-96 shadow-xl rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-ban text-red-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-3">Refuser la réservation</h3>
            <div class="mt-3 px-7 py-4">
                <p class="text-gray-600">
                    Êtes-vous sûr de vouloir refuser cette réservation ?
                </p>
            </div>
            <form action="{{ route('bookings.refuse', $booking) }}" method="POST" class="mt-5">
                @csrf
                <div class="mb-5">
                    <label for="refusal_reason" class="block text-sm font-medium text-gray-700 mb-2 text-left">Raison du refus (optionnel)</label>
                    <textarea name="refusal_reason" id="refusal_reason" rows="3" 
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                              placeholder="Expliquez pourquoi vous refusez cette réservation..."></textarea>
                </div>
                <div class="flex justify-center gap-3">
                    <button type="button" onclick="closeRefuseModal()" 
                            class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition duration-200">
                        Confirmer le refus
                    </button>
                </div>
            </form>
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

// Close modal when clicking outside
window.onclick = function(event) {
    const cancelModal = document.getElementById('cancelModal');
    const refuseModal = document.getElementById('refuseModal');
    
    if (event.target === cancelModal) {
        closeCancelModal();
    }
    if (event.target === refuseModal) {
        closeRefuseModal();
    }
}
</script>