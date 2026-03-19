@extends('layouts.app')

@section('title', 'Profil livreur - ' . ($driver->full_name ?? $driver->first_name))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Retour --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('prestataire.drivers.index') }}" 
               class="inline-flex items-center text-gray-600 hover:text-orange-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour à la liste
            </a>
            @if($driver->is_available ?? false)
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    Disponible
                </span>
            @else
                <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    Indisponible
                </span>
            @endif
        </div>

        {{-- Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Colonne gauche - Profil --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Carte profil --}}
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-500 to-amber-500 p-6 text-center">
                        <div class="w-24 h-24 bg-white rounded-full mx-auto flex items-center justify-center shadow-lg">
                            @if($driver->photo)
                                <img src="{{ asset('storage/' . $driver->photo) }}" 
                                     alt="{{ $driver->full_name ?? $driver->first_name }}" 
                                     class="w-24 h-24 rounded-full object-cover">
                            @else
                                <span class="text-3xl font-bold text-orange-600">
                                    {{ strtoupper(substr($driver->first_name ?? '', 0, 1) . substr($driver->last_name ?? '', 0, 1)) }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-xl font-bold text-white mt-4">
                            {{ $driver->full_name ?? $driver->first_name . ' ' . $driver->last_name }}
                        </h1>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            @php
                                $vehicleIcons = [
                                    'scooter' => '🛵',
                                    'moto' => '🏍️',
                                    'motorcycle' => '🏍️',
                                    'velo' => '🚴',
                                    'bike' => '🚴',
                                    'voiture' => '🚗',
                                    'car' => '🚗',
                                    'van' => '🚐',
                                ];
                                $vehicleType = $driver->vehicle_type ?? 'scooter';
                                $vehicleIcon = $vehicleIcons[strtolower($vehicleType)] ?? '🛵';
                            @endphp
                            <span class="text-2xl">{{ $vehicleIcon }}</span>
                            <span class="text-white/80 capitalize">{{ $vehicleType }}</span>
                        </div>
                    </div>

                    <div class="p-5">
                        {{-- Note globale --}}
                        <div class="text-center mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center justify-center gap-1 mb-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-lg {{ $i <= ($driver->rating ?? 0) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                @endfor
                            </div>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($driver->rating ?? 0, 1) }}/5</p>
                            <p class="text-sm text-gray-500">Note globale</p>
                        </div>

                        {{-- Stats --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xl font-bold text-gray-900">{{ $driver->completed_deliveries ?? $driver->total_deliveries ?? 0 }}</p>
                                <p class="text-xs text-gray-500">Livraisons</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xl font-bold text-green-600">{{ $driver->success_rate ?? 100 }}%</p>
                                <p class="text-xs text-gray-500">Taux réussite</p>
                            </div>
                        </div>

                        {{-- Info contact --}}
                        <div class="space-y-2 text-sm">
                            @if($driver->phone)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone w-5 text-gray-400"></i>
                                    <span>{{ $driver->phone }}</span>
                                </div>
                            @endif
                            @if($driver->email)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-envelope w-5 text-gray-400"></i>
                                    <span class="truncate">{{ $driver->email }}</span>
                                </div>
                            @endif
                            @if($driver->vehicle_plate)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-id-card w-5 text-gray-400"></i>
                                    <span>{{ $driver->vehicle_plate }}</span>
                                </div>
                            @endif
                            @if($driver->created_at)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-calendar w-5 text-gray-400"></i>
                                    <span>Inscrit {{ $driver->created_at->diffForHumans() }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Bio --}}
                        @if($driver->bio)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-600 italic">"{{ $driver->bio }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Colonne droite - Actions et préférences --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Actions rapides --}}
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-cogs text-orange-500 mr-2"></i>
                        Gérer ce livreur
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Favori --}}
                        <button type="button" 
                                onclick="setPreference('preferred')"
                                class="preference-btn flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all">
                            <i class="fas fa-star text-3xl text-gray-300 group-hover:text-green-500 mb-2"></i>
                            <span class="font-medium text-gray-700">Favori</span>
                            <span class="text-xs text-gray-500">Prioriser ce livreur</span>
                        </button>

                        {{-- Normal --}}
                        <button type="button" 
                                onclick="setPreference('neutral')"
                                class="preference-btn flex flex-col items-center p-4 border-2 border-orange-500 bg-orange-50 rounded-xl transition-all">
                            <i class="fas fa-user text-3xl text-orange-500 mb-2"></i>
                            <span class="font-medium text-gray-700">Normal</span>
                            <span class="text-xs text-gray-500">Aucune préférence</span>
                        </button>

                        {{-- Bloquer --}}
                        <button type="button" 
                                onclick="setPreference('blocked')"
                                class="preference-btn flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl hover:border-red-500 hover:bg-red-50 transition-all">
                            <i class="fas fa-ban text-3xl text-gray-300 group-hover:text-red-500 mb-2"></i>
                            <span class="font-medium text-gray-700">Bloquer</span>
                            <span class="text-xs text-gray-500">Ne plus travailler avec</span>
                        </button>
                    </div>
                </div>

                {{-- Donner une note --}}
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-star text-orange-500 mr-2"></i>
                        Donner une note
                    </h3>

                    <div class="space-y-4">
                        {{-- Étoiles --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Votre évaluation</label>
                            <div class="flex gap-2" id="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" 
                                            onclick="setRating({{ $i }})"
                                            class="star-btn p-2 rounded-lg hover:bg-orange-50 transition-all">
                                        <i class="fas fa-star text-3xl text-gray-300" data-star="{{ $i }}"></i>
                                    </button>
                                @endfor
                            </div>
                        </div>

                        {{-- Critères détaillés --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Rapidité</label>
                                <div class="flex gap-1" id="speed-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" onclick="setCriteria('speed', {{ $i }})" class="criteria-btn">
                                            <i class="fas fa-star text-sm text-gray-300" data-criteria="speed" data-star="{{ $i }}"></i>
                                        </button>
                                    @endfor
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Communication</label>
                                <div class="flex gap-1" id="comm-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" onclick="setCriteria('communication', {{ $i }})" class="criteria-btn">
                                            <i class="fas fa-star text-sm text-gray-300" data-criteria="communication" data-star="{{ $i }}"></i>
                                        </button>
                                    @endfor
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Présentation</label>
                                <div class="flex gap-1" id="presentation-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" onclick="setCriteria('presentation', {{ $i }})" class="criteria-btn">
                                            <i class="fas fa-star text-sm text-gray-300" data-criteria="presentation" data-star="{{ $i }}"></i>
                                        </button>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        {{-- Commentaire --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Commentaire (optionnel)</label>
                            <textarea id="rating-comment" rows="3" 
                                      placeholder="Décrivez votre expérience avec ce livreur..."
                                      class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"></textarea>
                        </div>

                        <button type="button" 
                                onclick="submitRating()"
                                class="w-full px-4 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-medium transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Envoyer mon évaluation
                        </button>
                    </div>
                </div>

                {{-- Notes internes --}}
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-sticky-note text-orange-500 mr-2"></i>
                        Notes internes
                        <span class="ml-2 text-xs text-gray-400 font-normal">(non visibles par le livreur)</span>
                    </h3>
                    <textarea id="internal-notes" rows="3" 
                              placeholder="Ajoutez des notes personnelles sur ce livreur..."
                              class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"></textarea>
                    <button type="button" 
                            onclick="saveNotes()"
                            class="mt-3 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Enregistrer les notes
                    </button>
                </div>

                {{-- Infos véhicule --}}
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-motorcycle text-orange-500 mr-2"></i>
                        Informations véhicule
                    </h3>
                    @php
                        $vehicleIcons = [
                            'scooter' => '🛵',
                            'moto' => '🏍️',
                            'motorcycle' => '🏍️',
                            'velo' => '🚴',
                            'bike' => '🚴',
                            'voiture' => '🚗',
                            'car' => '🚗',
                            'van' => '🚐',
                        ];
                        $vehicleType = $driver->vehicle_type ?? 'scooter';
                        $vehicleIcon = $vehicleIcons[strtolower($vehicleType)] ?? '🛵';
                    @endphp
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <p class="text-2xl mb-1">{{ $vehicleIcon }}</p>
                            <p class="text-sm text-gray-600 capitalize">{{ $driver->vehicle_type ?? 'Scooter' }}</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <i class="fas fa-id-card text-2xl text-gray-400 mb-1"></i>
                            <p class="text-sm text-gray-600">{{ $driver->vehicle_plate ?? 'N/A' }}</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <i class="fas fa-box text-2xl text-gray-400 mb-1"></i>
                            <p class="text-sm text-gray-600">{{ $driver->has_thermal_bag ? 'Sac isotherme ✓' : 'Sans sac' }}</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <i class="fas fa-route text-2xl text-gray-400 mb-1"></i>
                            <p class="text-sm text-gray-600">{{ $driver->max_distance ?? 10 }} km max</p>
                        </div>
                    </div>
                </div>

                {{-- Contacter --}}
                <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl shadow-lg p-5 text-white">
                    <h3 class="font-semibold mb-4 flex items-center">
                        <i class="fas fa-phone-alt mr-2"></i>
                        Contacter ce livreur
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        @if($driver->phone)
                            <a href="tel:{{ $driver->phone }}" 
                               class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                                <i class="fas fa-phone mr-2"></i>
                                Appeler
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $driver->phone) }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 rounded-lg transition-colors">
                                <i class="fab fa-whatsapp mr-2"></i>
                                WhatsApp
                            </a>
                            <a href="sms:{{ $driver->phone }}" 
                               class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                                <i class="fas fa-sms mr-2"></i>
                                SMS
                            </a>
                        @endif
                        @if($driver->email)
                            <a href="mailto:{{ $driver->email }}" 
                               class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                                <i class="fas fa-envelope mr-2"></i>
                                Email
                            </a>
                        @endif
                    </div>
                </div>

                {{-- EQUIPE INTERNE --}}
                @php
                    $isInternalForMe = (int) ($driver->employer_prestataire_id ?? 0) === (int) ($prestataire->id ?? 0);
                    $isInternalElsewhere = !empty($driver->employer_prestataire_id) && !$isInternalForMe;
                @endphp
                <div class="bg-white rounded-2xl shadow-lg p-5 border border-gray-100">
                    <h3 class="font-semibold mb-3 flex items-center text-gray-900">
                        <i class="fas fa-user-tie mr-2 text-purple-600"></i>
                        Equipe interne
                    </h3>

                    @if($isInternalForMe)
                        <div class="mb-3 px-3 py-2 rounded-lg bg-green-50 text-green-700 text-sm">
                            <i class="fas fa-check-circle mr-1"></i>
                            Ce livreur est dans votre equipe interne.
                        </div>
                        @php
                            $internalCode = $internalAccessCode ?? null;
                            $internalAccessUrl = route('driver.internal.access', ['redirect_to' => '/prestataire/food/food-orders/internal-map']);
                            $internalAccessMessage = trim("Connexion livreur interne Taprestation\nURL: {$internalAccessUrl}\nCode: {$internalCode}\n\n1) Ouvrir le lien\n2) Entrer le code\n3) Acceder a la carte de tournee");
                        @endphp
                        <div class="mb-3 px-3 py-3 rounded-lg bg-indigo-50 text-indigo-800 text-sm border border-indigo-100">
                            <div class="font-semibold mb-1">Code d'accès livreur (mobile)</div>
                            <div class="text-lg font-bold tracking-wide">{{ $internalCode ?: 'Non généré' }}</div>
                            <div class="mt-1 text-xs text-indigo-700">
                                URL: <code>{{ $internalAccessUrl }}</code>
                            </div>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <button type="button"
                                        onclick='copyDriverAccess(@json($internalCode ?: ""), "Code copié")'
                                        class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-semibold transition-colors">
                                    <i class="fas fa-copy mr-1"></i> Copier code
                                </button>
                                <button type="button"
                                        onclick='copyDriverAccess(@json($internalAccessUrl), "URL copiée")'
                                        class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-semibold transition-colors">
                                    <i class="fas fa-link mr-1"></i> Copier URL
                                </button>
                                <button type="button"
                                        onclick='copyDriverAccess(@json($internalAccessMessage), "Message copié")'
                                        class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-semibold transition-colors">
                                    <i class="fas fa-paper-plane mr-1"></i> Copier message
                                </button>
                            </div>
                        </div>
                        <form action="{{ route('prestataire.drivers.regenerate-code', $driver->id) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold transition-colors">
                                <i class="fas fa-key mr-2"></i>
                                Régénérer le code livreur
                            </button>
                        </form>
                        @if($driver->user_id && $driver->user)
                            <div class="mb-3 px-3 py-2 rounded-lg bg-blue-50 text-blue-700 text-sm">
                                <i class="fas fa-link mr-1"></i>
                                Compte lié: {{ $driver->user->name ?? 'Utilisateur' }} ({{ $driver->user->email }})
                                <br>
                                Accès dashboard classique: <code>{{ url('/driver/dashboard') }}</code>
                            </div>
                        @else
                            <div class="mb-3 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm">
                                <i class="fas fa-user-clock mr-1"></i>
                                Aucun compte lié.
                                <strong>Ce n'est pas bloquant</strong>: le livreur peut se connecter avec le code ci-dessus via l'URL d'accès interne.
                                Lier un compte client par email est optionnel (pour accès dashboard classique).
                            </div>
                            <form action="{{ route('prestataire.drivers.link-user', $driver->id) }}" method="POST" class="mb-3">
                                @csrf
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Email du compte client à lier (optionnel)</label>
                                <div class="flex gap-2">
                                    <input type="email"
                                           name="user_email"
                                           required
                                           placeholder="email@exemple.com"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                    <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors">
                                        Lier
                                    </button>
                                </div>
                            </form>
                        @endif
                        <form action="{{ route('prestataire.drivers.detach-internal', $driver->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 font-semibold transition-colors">
                                <i class="fas fa-user-minus mr-2"></i>
                                Retirer de mon equipe interne
                            </button>
                        </form>
                    @elseif($isInternalElsewhere)
                        <div class="px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm">
                            <i class="fas fa-info-circle mr-1"></i>
                            Ce livreur est deja interne chez un autre prestataire.
                        </div>
                    @else
                        <div class="mb-3 px-3 py-2 rounded-lg bg-purple-50 text-purple-700 text-sm">
                            <i class="fas fa-info-circle mr-1"></i>
                            Associez votre collegue ici pour les livraisons internes (tarifs prestataire).
                        </div>
                        <form action="{{ route('prestataire.drivers.attach-internal', $driver->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold transition-colors">
                                <i class="fas fa-user-plus mr-2"></i>
                                Ajouter a mon equipe interne
                            </button>
                        </form>
                    @endif
                </div>

                {{-- PARRAINER CE LIVREUR --}}
                @if(($driver->trust_level ?? 'probation') === 'probation' && !$driver->sponsor_prestataire_id && empty($driver->employer_prestataire_id))
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-lg p-5 text-white">
                    <h3 class="font-semibold mb-3 flex items-center">
                        <i class="fas fa-handshake mr-2"></i>
                        Parrainer ce livreur
                    </h3>
                    <p class="text-sm text-white/80 mb-4">
                        Ce livreur est en période d'essai. En le parrainant, vous lui permettez d'accéder à plus de commandes 
                        et vous serez notifié de ses performances.
                    </p>
                    <div class="bg-white/10 rounded-lg p-3 mb-4">
                        <div class="flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check"></i>
                                <span>Max 5 cmd/jour (au lieu de 3)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check"></i>
                                <span>Max 100€/cmd (au lieu de 50€)</span>
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('prestataire.drivers.sponsor', $driver->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-white text-blue-600 rounded-lg hover:bg-blue-50 font-semibold transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>
                            Parrainer {{ $driver->first_name }}
                        </button>
                    </form>
                </div>
                @elseif($driver->sponsor_prestataire_id === ($prestataire->id ?? 0))
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-lg p-5 text-white">
                    <h3 class="font-semibold mb-2 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Vous parrainez ce livreur
                    </h3>
                    <p class="text-sm text-white/80">
                        Parrainé le {{ $driver->sponsored_at ? $driver->sponsored_at->format('d/m/Y') : 'récemment' }}. 
                        Il bénéficie de limites augmentées grâce à votre parrainage.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentRating = 0;
    let currentCriteria = { speed: 0, communication: 0, presentation: 0 };
    const driverId = {{ $driver->id }};

    function copyDriverAccess(text, successMessage = 'Copié') {
        const value = String(text || '').trim();
        if (!value) {
            alert('Aucune donnée à copier.');
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value)
                .then(() => alert(successMessage))
                .catch(() => fallbackCopyDriverAccess(value, successMessage));
            return;
        }

        fallbackCopyDriverAccess(value, successMessage);
    }

    function fallbackCopyDriverAccess(value, successMessage) {
        const ta = document.createElement('textarea');
        ta.value = value;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        ta.style.top = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try {
            document.execCommand('copy');
            alert(successMessage);
        } catch (e) {
            alert('Copie impossible sur ce navigateur.');
        }
        document.body.removeChild(ta);
    }

    function setRating(rating) {
        currentRating = rating;
        document.querySelectorAll('#rating-stars .fa-star').forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-orange-500');
            } else {
                star.classList.remove('text-orange-500');
                star.classList.add('text-gray-300');
            }
        });
    }

    function setCriteria(criteria, rating) {
        currentCriteria[criteria] = rating;
        document.querySelectorAll(`[data-criteria="${criteria}"]`).forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }

    function setPreference(status) {
        // Mettre à jour l'UI
        document.querySelectorAll('.preference-btn').forEach(btn => {
            btn.classList.remove('border-orange-500', 'bg-orange-50', 'border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50');
            btn.classList.add('border-gray-200');
        });
        
        const colors = {
            'preferred': ['border-green-500', 'bg-green-50'],
            'neutral': ['border-orange-500', 'bg-orange-50'],
            'blocked': ['border-red-500', 'bg-red-50']
        };
        
        event.currentTarget.classList.remove('border-gray-200');
        event.currentTarget.classList.add(...colors[status]);

        // Afficher un message de confirmation
        alert('Préférence mise à jour : ' + (status === 'preferred' ? 'Favori' : status === 'blocked' ? 'Bloqué' : 'Normal'));
    }

    function submitRating() {
        if (currentRating === 0) {
            alert('Veuillez sélectionner une note');
            return;
        }
        
        const comment = document.getElementById('rating-comment').value;
        
        // Afficher un message de confirmation
        alert('Merci pour votre évaluation ! Note : ' + currentRating + '/5');
        
        // Réinitialiser
        currentRating = 0;
        currentCriteria = { speed: 0, communication: 0, presentation: 0 };
        document.querySelectorAll('.fa-star').forEach(star => {
            star.classList.remove('text-orange-500', 'text-yellow-400');
            star.classList.add('text-gray-300');
        });
        document.getElementById('rating-comment').value = '';
    }

    function saveNotes() {
        const notes = document.getElementById('internal-notes').value;
        alert('Notes enregistrées !');
    }
</script>
@endpush
@endsection
