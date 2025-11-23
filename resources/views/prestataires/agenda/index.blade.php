@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
    #calendar {
        border: none;
    }
    .fc-daygrid-day {
        height: 120px; /* Adjust height as needed */
        padding: 4px;
        position: relative;
    }
    .fc-daygrid-day-number {
        font-size: 0.8rem;
        font-weight: 400;
    }
    .fc-day-today .fc-daygrid-day-number {
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    .fc-event {
        font-size: 0.75rem;
        padding: 2px 4px;
        margin-bottom: 2px;
        border-radius: 4px;
        border: none;
        color: white;
    }
    .fc-event-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .fc-day-selected {
        background-color: #f0f0f0;
    }
    .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 500;
    }
    .fc-button {
        background-color: transparent !important;
        border: 1px solid #ddd !important;
        color: #333 !important;
    }
    .fc-button-primary:not(:disabled).fc-button-active, .fc-button-primary:not(:disabled):active {
        background-color: #e0e0e0 !important;
        border-color: #ccc !important;
    }
    .modal {
        display: none; 
        position: fixed; 
        z-index: 1050; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        position: fixed;
        right: 0;
        top: 0;
        height: 100%;
        width: 350px;
        background-color: #fff;
        border-left: 1px solid #ddd;
        padding: 20px;
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
    }
    .modal.show .modal-content {
        transform: translateX(0);
    }
    .close {
        color: #aaa;
        position: absolute;
        top: 10px;
        right: 25px;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">Mon Agenda</h1>

    <div class="flex">
        <div class="w-1/4 pr-4">
            <h2 class="text-xl font-bold mb-4">Demandes récentes</h2>
            <div class="mb-4">
                <input type="text" id="searchInput" class="w-full p-2 border rounded" placeholder="Rechercher...">
            </div>
            <div id="recent-requests" class="space-y-2">
                <!-- Recent requests will be loaded here -->
                <p>Vous n’avez reçu aucune demande récemment.</p>
            </div>
        </div>
        <div class="w-3/4">
            <div id='calendar'></div>
        </div>
    </div>
</div>

<!-- The Modal -->
<div id="eventModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2 id="eventTitle"></h2>
    <p><strong>Client:</strong> <span id="eventClient"></span></p>
    <p><strong>Service:</strong> <span id="eventService"></span></p>
    <p><strong>Statut:</strong> <span id="eventStatus"></span></p>
    <a id="eventLink" href="#" target="_blank" class="text-blue-500 hover:underline">Voir la demande</a>
  </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today:    'Aujourd\'hui',
                month:    'Mois',
                week:     'Semaine',
                day:      'Jour'
            },
            events: '{{ route('api.prestataire.agenda.events') }}',
            dayMaxEvents: 2, // Show a "+more" link if there are more than 2 events
            moreLinkClick: function(info) {
                calendar.gotoDate(info.date);
                calendar.changeView('timeGridDay');
            },
            eventDidMount: function(info) {
                // Custom rendering to add more details
                let eventEl = info.el;
                let event = info.event;
                let props = event.extendedProps;



                let contentEl = eventEl.querySelector('.fc-event-main-frame');
                if (contentEl) {
                    contentEl.innerHTML = `
                        <div class="fc-event-time" style="font-size: 0.7em; color: #eee;">${props.startTime}</div>
                        <div class="fc-event-title-container">
                            <div class="fc-event-title">${event.title}</div>
                            <div class="fc-event-client" style="font-size: 0.8em; font-weight: 500;">${props.clientName}</div>
                        </div>
                    `;
                }
            },
            dateClick: function(info) {
                // Logic to open side panel with day's details
                console.log('Clicked on: ' + info.dateStr);
                // You can implement the side panel logic here
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault(); // don't let the browser navigate

                var modal = document.getElementById('eventModal');
                document.getElementById('eventTitle').innerText = info.event.title;
                document.getElementById('eventClient').innerText = info.event.extendedProps.clientName || 'N/A';
                document.getElementById('eventService').innerText = info.event.extendedProps.serviceName || 'N/A';
                document.getElementById('eventStatus').innerText = info.event.extendedProps.status || 'N/A';
                var eventLink = document.getElementById('eventLink');
                if(info.event.extendedProps.bookingUrl) {
                    eventLink.href = info.event.extendedProps.bookingUrl;
                    eventLink.style.display = 'block';
                } else {
                    eventLink.style.display = 'none';
                }
                modal.style.display = "block";
                setTimeout(() => modal.classList.add('show'), 10); // Delay to ensure display:block is applied before transition
            }
        });
        calendar.render();

        // Modal close logic
        var modal = document.getElementById('eventModal');
        var span = document.getElementsByClassName("close")[0];

        function closeModal() {
            modal.classList.remove('show');
            // Wait for transition to finish before hiding
            setTimeout(() => {

        // Fetch recent requests
        fetch('{{ route('api.prestataire.agenda.recent-bookings') }}')
            .then(response => response.json())
            .then(data => {
                const requestsContainer = document.getElementById('recent-requests');
                if (data.length > 0) {
                    requestsContainer.innerHTML = ''; // Clear the default message
                    data.forEach(booking => {
                        const requestElement = document.createElement('div');
                        requestElement.className = 'p-2 border rounded ';
                        requestElement.innerHTML = `
                            <p class="font-bold">${booking.client.user.name}</p>
                            <p>${booking.service.title}</p>
                            <p class="text-sm text-gray-500">${booking.start_datetime ? new Date(booking.start_datetime).toLocaleDateString('fr-FR') : 'Non planifiée'}</p>
                            <p class="text-sm">Statut: ${booking.status}</p>
                            <a href="/prestataire/bookings/${booking.id}" class="text-blue-500 hover:underline">Voir plus</a>
                        `;
                        requestsContainer.appendChild(requestElement);

                        requestElement.addEventListener('click', () => {
                            if (booking.start_datetime) {
                                calendar.gotoDate(booking.start_datetime);
                            }
                        });
                    });
                } 
            });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const requests = document.querySelectorAll('#recent-requests > div');
            requests.forEach(request => {
                const clientName = request.querySelector('.font-bold').innerText.toLowerCase();
                if (clientName.includes(filter)) {
                    request.style.display = '';
                } else {
                    request.style.display = 'none';
                }
            });
        });
                modal.style.display = "none";
            }, 300); // Must match transition duration in CSS
        }

        span.onclick = function() {
            closeModal();
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    });
</script>
@endpush