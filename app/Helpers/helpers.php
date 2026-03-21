<?php

use Carbon\Carbon;
use App\Models\Prestataire;
use App\Models\Booking;
use App\Models\Service;

if (!function_exists('generate_time_slots')) {
    function generate_time_slots(Prestataire $prestataire, Carbon $startDate, Carbon $endDate)
    {
        $slots = [];
        $availabilities = $prestataire->availabilities()->where('is_active', true)->get();
        // Consider pending + confirmed bookings as blocking to avoid overlap
        $blockingBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();
        // Get all bookings (including pending) for display purposes
        $allBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Utiliser dayOfWeek (0=dimanche, 1=lundi, etc.) au lieu de dayOfWeekIso
            $dayOfWeek = $date->dayOfWeek;
            $availability = $availabilities->firstWhere('day_of_week', $dayOfWeek);

            if ($availability) {
                $sTime = Carbon::parse($availability->start_time);
                $eTime = Carbon::parse($availability->end_time);
                $startTime = $date->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                $endTime = $date->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                $slotDuration = (int) ($availability->slot_duration ?? 60);
                if ($slotDuration < 1) $slotDuration = 60;

                for ($slotTime = $startTime->copy(); $slotTime->lt($endTime); $slotTime->addMinutes($slotDuration)) {
                    $slotEnd = $slotTime->copy()->addMinutes($slotDuration);

                    // Check if slot is booked by a blocking booking (pending/confirmed)
                    $blockingBooking = $blockingBookings->first(function ($booking) use ($slotTime, $slotEnd) {
                        // Check if the slot overlaps with a blocking booking
                        if ($booking->start_datetime == $booking->end_datetime) {
                            return $booking->start_datetime >= $slotTime && $booking->start_datetime < $slotEnd;
                        }
                        return ($booking->start_datetime < $slotEnd) && ($booking->end_datetime > $slotTime);
                    });
                    
                    // Check if slot has any booking (for display info)
                    $anyBooking = $allBookings->first(function ($booking) use ($slotTime, $slotEnd) {
                        if ($booking->start_datetime == $booking->end_datetime) {
                            return $booking->start_datetime >= $slotTime && $booking->start_datetime < $slotEnd;
                        }
                        return ($booking->start_datetime < $slotEnd) && ($booking->end_datetime > $slotTime);
                    });

                    $isBreak = false;
                    if ($availability->break_start_time && $availability->break_end_time) {
                        $breakStartTime = Carbon::parse($availability->break_start_time);
                        $breakEndTime = Carbon::parse($availability->break_end_time);
                        $breakStart = $date->copy()->hour($breakStartTime->hour)->minute($breakStartTime->minute)->second($breakStartTime->second);
                        $breakEnd = $date->copy()->hour($breakEndTime->hour)->minute($breakEndTime->minute)->second($breakEndTime->second);

                        // Check if the slot overlaps with a break
                        if (($slotTime < $breakEnd) && ($slotEnd > $breakStart)) {
                            $isBreak = true;
                        }
                    }

                    // Include all slots with their status
                    if (!$isBreak) {
                        $slots[] = [
                            'datetime' => $slotTime->copy(),
                            'end_datetime' => $slotEnd->copy(),
                            'duration' => $slotDuration,
                            'is_booked' => (bool) $blockingBooking,
                            'has_pending' => $anyBooking && $anyBooking->status === 'pending',
                            'booking_status' => $anyBooking ? $anyBooking->status : null,
                            'booking_id' => $anyBooking ? $anyBooking->id : null,
                            'break_start_time' => $availability->break_start_time,
                            'break_end_time' => $availability->break_end_time,
                            'availability_start' => $availability->start_time,
                            'availability_end' => $availability->end_time
                        ];
                    }
                }
            }
        }

        return $slots;
    }
}

if (!function_exists('generate_time_slots_for_service')) {
    function generate_time_slots_for_service(Service $service, Carbon $startDate, Carbon $endDate)
    {
        $prestataire = $service->prestataire;
        $slots = [];
        $availabilities = $prestataire->availabilities()->where('is_active', true)->get();
        // Consider pending + confirmed bookings as blocking to avoid overlap
        $blockingBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();
        // Get all bookings (including pending) for display purposes
        $allBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();

        // Calculate service duration in minutes from estimated_duration + duration_unit OR fallback to quantity
        $serviceDurationMinutes = 60; // Default to 60 minutes
        
        // Priority 1: Use new estimated_duration + duration_unit fields
        if ($service->estimated_duration && $service->duration_unit) {
            switch ($service->duration_unit) {
                case 'minutes':
                    $serviceDurationMinutes = $service->estimated_duration;
                    break;
                case 'hours':
                    $serviceDurationMinutes = $service->estimated_duration * 60;
                    break;
                case 'days':
                    $serviceDurationMinutes = $service->estimated_duration * 60 * 24; // Full days in minutes
                    break;
            }
        }
        // Priority 2: Fallback to old quantity field for hourly services
        elseif ($service->price_type === 'heure' && $service->quantity) {
            $serviceDurationMinutes = $service->quantity * 60;
        }
        
        // Get buffer time (time between reservations) - default 15 min if not set
        $bufferTime = $service->buffer_time ?? 15;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Utiliser dayOfWeek (0=dimanche, 1=lundi, etc.) au lieu de dayOfWeekIso
            $dayOfWeek = $date->dayOfWeek;
            $availability = $availabilities->firstWhere('day_of_week', $dayOfWeek);

            if ($availability) {
                $sTime = Carbon::parse($availability->start_time);
                $eTime = Carbon::parse($availability->end_time);
                $startTime = $date->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                $endTime = $date->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                
                // Check if service has a specific duration (from estimated_duration or quantity)
                $hasSpecificDuration = ($service->estimated_duration && $service->duration_unit) || 
                                       ($service->price_type === 'heure' && $service->quantity);
                
                // Generate slots based on service duration
                if ($hasSpecificDuration) {
                    // Calculate total available time (excluding break time)
                    $totalWorkMinutes = $startTime->diffInMinutes($endTime);
                    
                    // Check for break time - use break_start/break_end (correct field names from model)
                    if ($availability->break_start && $availability->break_end) {
                        $breakStartTime = Carbon::parse($availability->break_start);
                        $breakEndTime = Carbon::parse($availability->break_end);
                        $breakStart = $date->copy()->hour($breakStartTime->hour)->minute($breakStartTime->minute)->second(0);
                        $breakEnd = $date->copy()->hour($breakEndTime->hour)->minute($breakEndTime->minute)->second(0);
                        $breakDuration = $breakStart->diffInMinutes($breakEnd);
                        $totalWorkMinutes -= $breakDuration;
                    }
                    
                    // Check if the service can fit in the total available time
                    if ($totalWorkMinutes >= $serviceDurationMinutes) {
                        // Create slots that represent the full service duration
                        $slotDuration = $serviceDurationMinutes;
                        
                        // Parse break times once if they exist
                        $breakStart = null;
                        $breakEnd = null;
                        if ($availability->break_start && $availability->break_end) {
                            $breakStartTime = Carbon::parse($availability->break_start);
                            $breakEndTime = Carbon::parse($availability->break_end);
                            $breakStart = $date->copy()->hour($breakStartTime->hour)->minute($breakStartTime->minute)->second(0);
                            $breakEnd = $date->copy()->hour($breakEndTime->hour)->minute($breakEndTime->minute)->second(0);
                        }
                        
                        // Generate slots based on the service duration using while loop
                        // We advance by 1 hour (60 min) to offer all possible start times
                        $slotTime = $startTime->copy();
                        $stepMinutes = 60; // Advance by 1 hour to show all possible slots
                        $maxIterations = 50; // Safety limit
                        $iteration = 0;

                        while ($slotTime->lt($endTime) && $iteration < $maxIterations) {
                            $iteration++;
                            $slotEnd = $slotTime->copy()->addMinutes($slotDuration);

                            // If there's a break and the service would overlap it, skip to after break
                            if ($breakStart && $breakEnd) {
                                if ($slotTime->lt($breakEnd) && $slotEnd->gt($breakStart)) {
                                    // Move to after the break and recalculate
                                    $slotTime = $breakEnd->copy();
                                    continue;
                                }
                            }

                            // Ensure the slot doesn't extend beyond the working day
                            if ($slotEnd->lte($endTime)) {
                                // Check if slot is booked by a blocking booking (pending/confirmed)
                                $blockingBooking = $blockingBookings->first(function ($booking) use ($slotTime, $slotEnd) {
                                    return ($booking->start_datetime < $slotEnd) && ($booking->end_datetime > $slotTime);
                                });

                                // Check if slot has any booking (for display info)
                                $anyBooking = $allBookings->first(function ($booking) use ($slotTime, $slotEnd) {
                                    return ($booking->start_datetime < $slotEnd) && ($booking->end_datetime > $slotTime);
                                });

                                // Include slot with its status
                                $slots[] = [
                                    'datetime' => $slotTime->copy(),
                                    'end_datetime' => $slotEnd->copy(),
                                    'duration' => $serviceDurationMinutes,
                                    'is_booked' => (bool) $blockingBooking,
                                    'has_pending' => $anyBooking && $anyBooking->status === 'pending',
                                    'booking_status' => $anyBooking ? $anyBooking->status : null,
                                    'booking_id' => $anyBooking ? $anyBooking->id : null,
                                    'break_start' => $availability->break_start,
                                    'break_end' => $availability->break_end,
                                    'availability_start' => $availability->start_time,
                                    'availability_end' => $availability->end_time
                                ];
                            }

                            // Move to next slot (advance by 1 hour for granular choices)
                            $slotTime->addMinutes($stepMinutes);
                        }
                    }
                } else {
                    // For non-hourly services or hourly services without specific duration, use standard slot generation
                    $slotDuration = $availability->slot_duration;

                    // Generate slots based on the appropriate duration
                    for ($slotTime = $startTime->copy(); $slotTime->lt($endTime); $slotTime->addMinutes($slotDuration)) {
                        $slotEnd = $slotTime->copy()->addMinutes($slotDuration);

                        // Check if the slot would go beyond the availability end time
                        if ($slotEnd->gt($endTime)) {
                            continue; // Skip this slot as it would extend beyond working hours
                        }

                        // Check if the slot overlaps with a break
                        $hasBreakConflict = false;
                        if ($availability->break_start_time && $availability->break_end_time) {
                            $breakStartTime = Carbon::parse($availability->break_start_time);
                            $breakEndTime = Carbon::parse($availability->break_end_time);
                            $breakStart = $date->copy()->hour($breakStartTime->hour)->minute($breakStartTime->minute)->second($breakStartTime->second);
                            $breakEnd = $date->copy()->hour($breakEndTime->hour)->minute($breakEndTime->minute)->second($breakEndTime->second);
                            
                            // Check if the slot overlaps with a break
                            if (($slotTime < $breakEnd) && ($slotEnd > $breakStart)) {
                                $hasBreakConflict = true;
                            }
                        }
                        
                        // Skip this slot if there's a break conflict
                        if ($hasBreakConflict) {
                            continue;
                        }

                        // Check if slot is booked by a blocking booking (pending/confirmed)
                        $blockingBooking = $blockingBookings->first(function ($booking) use ($slotTime, $slotEnd) {
                            // Check if the slot overlaps with a blocking booking
                            if ($booking->start_datetime == $booking->end_datetime) {
                                return $booking->start_datetime >= $slotTime && $booking->start_datetime < $slotEnd;
                            }
                            return ($booking->start_datetime < $slotEnd) && ($booking->end_datetime > $slotTime);
                        });
                        
                        // Check if slot has any booking (for display info)
                        $anyBooking = $allBookings->first(function ($booking) use ($slotTime, $slotEnd) {
                            if ($booking->start_datetime == $booking->end_datetime) {
                                return $booking->start_datetime >= $slotTime && $booking->start_datetime < $slotEnd;
                            }
                            return ($booking->start_datetime < $slotEnd) && ($booking->end_datetime > $slotTime);
                        });

                        // Include all slots with their status
                        $slots[] = [
                            'datetime' => $slotTime->copy(),
                            'end_datetime' => $slotEnd->copy(),
                            'duration' => $slotDuration,
                            'is_booked' => (bool) $blockingBooking,
                            'has_pending' => $anyBooking && $anyBooking->status === 'pending',
                            'booking_status' => $anyBooking ? $anyBooking->status : null,
                            'booking_id' => $anyBooking ? $anyBooking->id : null,
                            'break_start_time' => $availability->break_start_time,
                            'break_end_time' => $availability->break_end_time,
                            'availability_start' => $availability->start_time,
                            'availability_end' => $availability->end_time
                        ];
                    }
                }
            }
        }

        // Remove duplicate slots
        $uniqueSlots = [];
        $seen = [];
        foreach ($slots as $slot) {
            $key = $slot['datetime']->format('Y-m-d H:i') . '-' . $slot['end_datetime']->format('H:i');
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $uniqueSlots[] = $slot;
            }
        }

        return $uniqueSlots;
    }
}

if (!function_exists('generate_daily_slots_for_service')) {
    function generate_daily_slots_for_service(Service $service, Carbon $startDate, Carbon $endDate)
    {
        $prestataire = $service->prestataire;
        $slots = [];
        $availabilities = $prestataire->availabilities()->where('is_active', true)->get();
        // Consider pending + confirmed bookings as blocking to avoid overlap
        $blockingBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();
        // Get all bookings (including pending) for display purposes
        $allBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();

        // For daily services with specific duration (number of days)
        $serviceDurationDays = $service->quantity ?? 1; // Default to 1 day if not set

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Utiliser dayOfWeek (0=dimanche, 1=lundi, etc.) au lieu de dayOfWeekIso
            $dayOfWeek = $date->dayOfWeek;
            $availability = $availabilities->firstWhere('day_of_week', $dayOfWeek);

            if ($availability) {
                $sTime = Carbon::parse($availability->start_time);
                $eTime = Carbon::parse($availability->end_time);
                $startTime = $date->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                $endTime = $date->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                
                // Calculate the end date for the service duration
                $serviceEndDate = $date->copy()->addDays($serviceDurationDays - 1);
                
                // Check if the service duration would extend beyond the end date
                if ($serviceEndDate->gt($endDate)) {
                    continue; // Skip this slot as it would extend beyond our date range
                }
                
                // Check if all days in the service duration are available
                $allDaysAvailable = true;
                
                for ($i = 0; $i < $serviceDurationDays; $i++) {
                    $checkDate = $date->copy()->addDays($i);
                    $checkDayOfWeek = $checkDate->dayOfWeek;
                    $checkAvailability = $availabilities->firstWhere('day_of_week', $checkDayOfWeek);
                    
                    if (!$checkAvailability) {
                        $allDaysAvailable = false;
                        break;
                    }
                }
                
                if (!$allDaysAvailable) {
                    continue; // Skip this slot as not all days are available
                }
                
                // Check if any of the days in the service duration are already booked
                $isBooked = false;
                for ($i = 0; $i < $serviceDurationDays; $i++) {
                    $checkDate = $date->copy()->addDays($i);
                    $checkStart = $checkDate->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                    $checkEnd = $checkDate->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                    
                    // Check for blocking bookings that overlap with this day
                    $dayBooked = $blockingBookings->first(function ($booking) use ($checkStart, $checkEnd) {
                        return ($booking->start_datetime < $checkEnd) && ($booking->end_datetime > $checkStart);
                    });
                    
                    if ($dayBooked) {
                        $isBooked = true;
                        break;
                    }
                }
                
                // Check if any of the days have pending bookings (for display info)
                $hasPending = false;
                for ($i = 0; $i < $serviceDurationDays; $i++) {
                    $checkDate = $date->copy()->addDays($i);
                    $checkStart = $checkDate->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                    $checkEnd = $checkDate->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                    
                    // Check for any bookings that overlap with this day
                    $dayBooking = $allBookings->first(function ($booking) use ($checkStart, $checkEnd) {
                        return ($booking->start_datetime < $checkEnd) && ($booking->end_datetime > $checkStart);
                    });
                    
                    if ($dayBooking && $dayBooking->status === 'pending') {
                        $hasPending = true;
                        break;
                    }
                }
                
                // Include slot with its status
                $slots[] = [
                    'datetime' => $startTime->copy(),
                    'end_datetime' => $startTime->copy()->addDays($serviceDurationDays),
                    'duration' => $serviceDurationDays * 24 * 60, // Duration in minutes
                    'is_booked' => $isBooked,
                    'has_pending' => $hasPending,
                    'booking_status' => null, // We'll determine this when needed
                    'booking_id' => null, // We'll determine this when needed
                    'break_start_time' => $availability->break_start_time,
                    'break_end_time' => $availability->break_end_time,
                    'availability_start' => $availability->start_time,
                    'availability_end' => $availability->end_time,
                    'service_duration_days' => $serviceDurationDays
                ];
            }
        }

        return $slots;
    }
}

if (!function_exists('get_admin_page_title')) {
    /**
     * Get the title for the current admin page based on the route.
     *
     * @return string
     */
    function get_admin_page_title(): string
    {
        $titleMap = [
            'administrateur.dashboard' => 'Tableau de bord',
            'administrateur.users.*' => 'Gestion des utilisateurs',
            'administrateur.prestataires.*' => 'Gestion des prestataires',
            'administrateur.clients.*' => 'Gestion des clients',
            'administrateur.services.*' => 'Modération des services',
            'administrateur.reviews.*' => 'Modération des avis',
        ];

        foreach ($titleMap as $pattern => $title) {
            if (request()->routeIs($pattern)) {
                return $title;
            }
        }

        return 'Administration';
    }
}

/**
 * Obtenir l'URL optimisée d'une image
 * 
 * @param string $path Chemin de l'image originale (ex: services/image.jpg)
 * @param string $size Taille désirée: 'thumb' (150px), 'medium' (600px), 'large' (1200px), 'original'
 * @return string URL de l'image
 */
if (!function_exists('optimized_image')) {
    function optimized_image(?string $path, string $size = 'medium'): string
    {
        if (empty($path)) {
            return asset('images/placeholder.png');
        }
        
        // Si on demande l'original
        if ($size === 'original') {
            return asset('storage/' . $path);
        }
        
        // Chercher la variante optimisée
        return \App\Services\ImageOptimizationService::getVariantUrl($path, $size);
    }
}

if (!function_exists('normalize_storage_asset_path')) {
    function normalize_storage_asset_path(?string $path, bool $preferRenderedDemo = true): ?string
    {
        if (empty($path)) {
            return null;
        }

        $hosts = array_filter([
            strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
            strtolower((string) request()->getHost()),
        ]);

        return \App\Support\DemoMarketplaceImage::normalizePath($path, $hosts, $preferRenderedDemo);
    }
}

if (!function_exists('storage_asset_url')) {
    function storage_asset_url(?string $path, ?string $fallback = null): string
    {
        if (empty($path)) {
            return $fallback ? asset($fallback) : asset('images/placeholder.svg');
        }

        $cleanPath = normalize_storage_asset_path($path, true);

        if (empty($cleanPath)) {
            return $fallback ? asset($fallback) : asset('images/placeholder.svg');
        }

        if (preg_match('/^data:/i', $cleanPath)) {
            return $cleanPath;
        }

        if (preg_match('/^https?:\/\//i', $cleanPath)) {
            return $cleanPath;
        }

        if (str_starts_with($cleanPath, 'demo-marketplace/')) {
            return asset('storage/' . $cleanPath) . '?v=demo-marketplace-rendered-20260309';
        }

        return asset('serve-image.php?path=' . rawurlencode($cleanPath));
    }
}

/**
 * Obtenir l'URL du thumbnail vidéo
 */
if (!function_exists('video_thumbnail')) {
    function video_thumbnail(?string $thumbnailPath, ?string $videoPath = null): string
    {
        if (!empty($thumbnailPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($thumbnailPath)) {
            return asset('storage/' . $thumbnailPath);
        }
        
        // Placeholder par défaut
        return asset('images/video-placeholder.png');
    }
}

/**
 * Récupérer un paramètre du site
 */
if (!function_exists('get_setting')) {
    function get_setting(string $key, $default = null)
    {
        try {
            // Cache plus court (10 secondes) et rafraîchissement forcé disponible
            $cacheKey = 'site_settings_v2';
            
            // Forcer le rechargement si demandé
            if (request()->has('_refresh_settings')) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }
            
            $settings = \Illuminate\Support\Facades\Cache::remember($cacheKey, 10, function () {
                return \Illuminate\Support\Facades\DB::table('settings')
                    ->pluck('value', 'key')
                    ->toArray();
            });
            
            return $settings[$key] ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

/**
 * Vider le cache des settings (à appeler après modification)
 */
if (!function_exists('clear_settings_cache')) {
    function clear_settings_cache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('site_settings');
        \Illuminate\Support\Facades\Cache::forget('site_settings_v2');
    }
}

/**
 * Vérifier si le mode abonnement est activé
 */
if (!function_exists('subscription_enabled')) {
    function subscription_enabled(): bool
    {
        return feature_enabled('subscription_enabled', false);
    }
}

/**
 * Obtenir le prix de l'abonnement
 */
if (!function_exists('subscription_price')) {
    function subscription_price(): float
    {
        return (float) get_setting('subscription_price', '29.99');
    }
}

/**
 * Obtenir les paramètres d'abonnement complets
 */
if (!function_exists('get_subscription_settings')) {
    function get_subscription_settings(): array
    {
        return [
            'enabled' => subscription_enabled(),
            'price' => subscription_price(),
            'currency' => get_setting('subscription_currency', 'EUR'),
            'duration' => (int) get_setting('subscription_duration', '30'),
            'trial_days' => (int) get_setting('subscription_trial_days', '0'),
            'description' => get_setting('subscription_description', 'Abonnement prestataire mensuel'),
        ];
    }
}

/**
 * Obtenir les paramètres de paiement
 * 
 * @return array
 */
if (!function_exists('get_payment_settings')) {
    function get_payment_settings(): array
    {
        return [
            // Moyens de paiement activés
            'stripe_enabled' => get_setting('stripe_enabled', '0') === '1',
            'cash_payment_enabled' => get_setting('cash_payment_enabled', '1') === '1',
            
            // Clés API Stripe: lues uniquement depuis la configuration serveur/.env
            'stripe_key' => (string) config('services.stripe.key', ''),
            'stripe_secret_configured' => filled((string) config('services.stripe.secret', '')),
            'stripe_webhook_secret_configured' => filled((string) config('stripe.webhook_secret', '')),
            'stripe_connect_enabled' => get_setting('stripe_connect_enabled', '0') === '1',
            
            // Paramètres généraux
            'currency' => get_setting('currency', 'EUR'),
            'min_payment_amount' => (float) get_setting('min_payment_amount', '1'),
            'min_withdrawal' => (float) get_setting('min_withdrawal', '50'),
            
            // Options
            'require_payment_before_booking' => get_setting('require_payment_before_booking', '0') === '1',
            'allow_partial_payment' => get_setting('allow_partial_payment', '1') === '1',
            'auto_refund_on_cancel' => get_setting('auto_refund_on_cancel', '0') === '1',
            'send_payment_receipts' => get_setting('send_payment_receipts', '1') === '1',
            
            // Acomptes et cautions
            'default_deposit_percent' => (int) get_setting('default_deposit_percent', '30'),
            'min_deposit_amount' => (float) get_setting('min_deposit_amount', '10'),
            'prestataire_can_set_deposit' => get_setting('prestataire_can_set_deposit', '1') === '1',
            'default_security_deposit' => (float) get_setting('default_security_deposit', '100'),
            'deposit_refund_delay' => (int) get_setting('deposit_refund_delay', '7'),
            'hold_deposit_instead_of_charge' => get_setting('hold_deposit_instead_of_charge', '1') === '1',
            
            // Commissions
            'commission_services' => (float) get_setting('commission_services', '10'),
            'commission_rentals' => (float) get_setting('commission_rentals', '8'),
            'commission_urgent_sales' => (float) get_setting('commission_urgent_sales', get_setting('commission_services', '10')),
            'commission_food' => (float) get_setting('commission_food', '15'),

            // Commissions côté client (frais ajoutés au prix)
            'commission_client_services' => (float) get_setting('commission_client_services', '0'),
            'commission_client_rentals' => (float) get_setting('commission_client_rentals', '0'),
            'commission_client_urgent_sales' => (float) get_setting('commission_client_urgent_sales', '0'),
            'commission_client_food' => (float) get_setting('commission_client_food', '0'),
            
            // Apple Pay / Google Pay
            'stripe_apple_pay' => get_setting('stripe_apple_pay', '1') === '1',
            'stripe_google_pay' => get_setting('stripe_google_pay', '1') === '1',
            
            // Klarna et Amazon Pay
            'stripe_klarna' => get_setting('stripe_klarna', '1') === '1',
            'amazon_pay_enabled' => get_setting('amazon_pay_enabled', '1') === '1',
            
            // Stripe Connect
            'stripe_platform_fee' => (float) get_setting('stripe_platform_fee', '5'),
            'stripe_payout_delay' => (int) get_setting('stripe_payout_delay', '7'),
        ];
    }
}

/**
 * Vérifier si un moyen de paiement est activé
 * 
 * @param string $method Le nom du moyen de paiement (stripe, cash, apple_pay, google_pay, klarna, amazon_pay)
 * @return bool
 */
if (!function_exists('payment_method_enabled')) {
    function payment_method_enabled(string $method): bool
    {
        $settings = get_payment_settings();
        
        return match($method) {
            'stripe' => $settings['stripe_enabled'],
            'cash' => $settings['cash_payment_enabled'],
            'apple_pay' => $settings['stripe_enabled'] && $settings['stripe_apple_pay'],
            'google_pay' => $settings['stripe_enabled'] && $settings['stripe_google_pay'],
            'klarna' => $settings['stripe_enabled'] && $settings['stripe_klarna'],
            'amazon_pay' => $settings['amazon_pay_enabled'],
            default => false,
        };
    }
}

/**
 * Obtenir les moyens de paiement activés pour les clients
 * 
 * @return array
 */
if (!function_exists('get_available_payment_methods')) {
    function get_available_payment_methods(): array
    {
        $methods = [];
        $settings = get_payment_settings();
        
        if ($settings['stripe_enabled']) {
            $methods['stripe'] = [
                'name' => 'Carte bancaire',
                'icon' => 'fas fa-credit-card',
                'description' => 'Visa, Mastercard, Amex',
            ];
            
            if ($settings['stripe_apple_pay']) {
                $methods['apple_pay'] = [
                    'name' => 'Apple Pay',
                    'icon' => 'fab fa-apple-pay',
                    'description' => 'Paiement rapide avec Apple',
                ];
            }
            
            if ($settings['stripe_google_pay']) {
                $methods['google_pay'] = [
                    'name' => 'Google Pay',
                    'icon' => 'fab fa-google-pay',
                    'description' => 'Paiement rapide avec Google',
                ];
            }
        }
        
        if ($settings['cash_payment_enabled']) {
            $methods['cash'] = [
                'name' => 'Espèces',
                'icon' => 'fas fa-money-bill-wave',
                'description' => 'Paiement à la livraison',
            ];
        }
        
        // Klarna (via Stripe)
        if ($settings['stripe_enabled'] && $settings['stripe_klarna']) {
            $methods['klarna'] = [
                'name' => 'Klarna',
                'icon' => 'fab fa-klarna',
                'description' => 'Payer en 3 fois sans frais',
            ];
        }
        
        // Amazon Pay
        if ($settings['amazon_pay_enabled']) {
            $methods['amazon_pay'] = [
                'name' => 'Amazon Pay',
                'icon' => 'fab fa-amazon',
                'description' => 'Payer avec votre compte Amazon',
            ];
        }
        
        return $methods;
    }
}

/**
 * Calculer le montant de l'acompte
 * 
 * @param float $totalAmount Le montant total
 * @param int|null $customPercent Pourcentage personnalisé (optionnel)
 * @return float
 */
if (!function_exists('calculate_deposit_amount')) {
    function calculate_deposit_amount(float $totalAmount, ?int $customPercent = null): float
    {
        $settings = get_payment_settings();
        $percent = $customPercent ?? $settings['default_deposit_percent'];
        
        $depositAmount = $totalAmount * ($percent / 100);
        
        // Respecter le montant minimum
        if ($depositAmount < $settings['min_deposit_amount']) {
            $depositAmount = min($settings['min_deposit_amount'], $totalAmount);
        }
        
        return round($depositAmount, 2);
    }
}

/**
 * Calculer la commission de la plateforme
 * 
 * @param float $amount Le montant de la transaction
 * @param string $type Le type de transaction (service, rental, food)
 * @return float
 */
if (!function_exists('calculate_platform_commission')) {
    function calculate_platform_commission(float $amount, string $type = 'service'): float
    {
        $settings = get_payment_settings();
        
        $rate = match($type) {
            'service', 'booking' => $settings['commission_services'],
            'rental', 'equipment' => $settings['commission_rentals'],
            'urgent_sale', 'urgent-sale', 'flash_sale', 'vente_flash' => $settings['commission_urgent_sales'] ?? $settings['commission_services'],
            'food' => $settings['commission_food'],
            default => $settings['commission_services'],
        };
        
        return round($amount * ($rate / 100), 2);
    }
}

/**
 * Vérifier si une fonctionnalité est activée
 * 
 * @param string $feature Le nom de la feature (sans le préfixe 'feature_')
 * @param bool $default Valeur par défaut si non trouvée
 * @return bool
 */
if (!function_exists('feature_enabled')) {
    function feature_enabled(string $feature, bool $default = false): bool
    {
        $key = 'feature_' . str_replace('feature_', '', $feature);
        return get_setting($key, $default ? '1' : '0') === '1';
    }
}

if (!function_exists('online_payments_enabled')) {
    function online_payments_enabled(): bool
    {
        if (!feature_enabled('payments_enabled', true)) {
            return false;
        }

        if (!feature_enabled('stripe_enabled', true)) {
            return false;
        }

        $settings = get_payment_settings();

        return (bool) ($settings['stripe_enabled'] ?? false);
    }
}

if (!function_exists('payment_feature_enabled')) {
    function payment_feature_enabled(?string $feature = null, bool $default = false): bool
    {
        if (!online_payments_enabled()) {
            return false;
        }

        if ($feature === null || $feature === '') {
            return true;
        }

        return feature_enabled($feature, $default);
    }
}

if (!function_exists('booking_online_payment_enabled')) {
    function booking_online_payment_enabled(): bool
    {
        return payment_feature_enabled('booking_payment_enabled', true);
    }
}

if (!function_exists('food_online_payment_enabled')) {
    function food_online_payment_enabled(): bool
    {
        return payment_feature_enabled('food_payment_enabled', true);
    }
}

if (!function_exists('checkout_payments_enabled')) {
    function checkout_payments_enabled(): bool
    {
        return payment_feature_enabled('cart_enabled', true)
            && payment_feature_enabled('checkout_enabled', true);
    }
}

if (!function_exists('prestataire_stripe_connect_enabled')) {
    function prestataire_stripe_connect_enabled(): bool
    {
        return payment_feature_enabled('prestataire_stripe_connect', false);
    }
}

if (!function_exists('cash_only_mode')) {
    function cash_only_mode(): bool
    {
        return !online_payments_enabled();
    }
}

if (!function_exists('normalize_payment_requirement_for_mode')) {
    function normalize_payment_requirement_for_mode(?string $paymentRequirement, string $fallback = 'none'): string
    {
        $paymentRequirement = $paymentRequirement ?: $fallback;

        return cash_only_mode() ? $fallback : $paymentRequirement;
    }
}

if (!function_exists('normalize_payment_policy_for_mode')) {
    function normalize_payment_policy_for_mode(?string $paymentPolicy, string $fallback = 'cash'): string
    {
        $paymentPolicy = $paymentPolicy ?: $fallback;

        return cash_only_mode() ? $fallback : $paymentPolicy;
    }
}

/**
 * Obtenir toutes les fonctionnalités avec leurs états
 * 
 * @return array
 */
if (!function_exists('get_all_features')) {
    function get_all_features(): array
    {
        return [
            // Paiements généraux
            'payments' => [
                'label' => 'Système de paiement',
                'icon' => '💳',
                'features' => [
                    'payments_enabled' => ['label' => 'Activer tous les paiements', 'enabled' => feature_enabled('payments_enabled', true)],
                    'stripe_enabled' => ['label' => 'Paiement Stripe (CB)', 'enabled' => feature_enabled('stripe_enabled', true)],
                ]
            ],
            // Abonnements
            'subscription' => [
                'label' => 'Abonnements',
                'icon' => '🔄',
                'features' => [
                    'subscription_enabled' => ['label' => 'Système d\'abonnement', 'enabled' => feature_enabled('subscription_enabled', false)],
                    'subscription_button_visible' => ['label' => 'Afficher bouton abonnement', 'enabled' => feature_enabled('subscription_button_visible', false)],
                ]
            ],
            // Connexion compte paiement prestataire
            'prestataire_payment' => [
                'label' => 'Paiement Prestataire',
                'icon' => '👨‍🍳',
                'features' => [
                    'prestataire_stripe_connect' => ['label' => 'Connexion Stripe Connect', 'enabled' => feature_enabled('prestataire_stripe_connect', false)],
                ]
            ],
            // Réservations
            'booking' => [
                'label' => 'Réservations',
                'icon' => '📅',
                'features' => [
                    'booking_payment_enabled' => ['label' => 'Paiement des réservations', 'enabled' => feature_enabled('booking_payment_enabled', true)],
                    'booking_deposit_enabled' => ['label' => 'Acompte sur réservation', 'enabled' => feature_enabled('booking_deposit_enabled', true)],
                ]
            ],
            // Food / Commandes
            'food' => [
                'label' => 'Commandes Food',
                'icon' => '🍔',
                'features' => [
                    'food_payment_enabled' => ['label' => 'Paiement commandes food', 'enabled' => feature_enabled('food_payment_enabled', true)],
                    'food_cash_enabled' => ['label' => 'Paiement espèces', 'enabled' => feature_enabled('food_cash_enabled', true)],
                ]
            ],
            // Panier
            'cart' => [
                'label' => 'Panier & Checkout',
                'icon' => '🛒',
                'features' => [
                    'cart_enabled' => ['label' => 'Panier d\'achat', 'enabled' => feature_enabled('cart_enabled', true)],
                    'checkout_enabled' => ['label' => 'Processus de checkout', 'enabled' => feature_enabled('checkout_enabled', true)],
                ]
            ],
            // Portefeuille
            'wallet' => [
                'label' => 'Portefeuille & Retraits',
                'icon' => '💰',
                'features' => [
                    'wallet_enabled' => ['label' => 'Portefeuille prestataire', 'enabled' => feature_enabled('wallet_enabled', false)],
                    'withdrawal_enabled' => ['label' => 'Demandes de retrait', 'enabled' => feature_enabled('withdrawal_enabled', false)],
                ]
            ],
        ];
    }
}
