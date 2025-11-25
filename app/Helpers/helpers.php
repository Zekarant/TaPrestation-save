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
        // Only consider confirmed bookings as "booked" - pending bookings should still allow new reservations
        $confirmedBookings = $prestataire->bookings()->where('status', 'confirmed')->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();
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
                $slotDuration = $availability->slot_duration;

                for ($slotTime = $startTime->copy(); $slotTime->lt($endTime); $slotTime->addMinutes($slotDuration)) {
                    $slotEnd = $slotTime->copy()->addMinutes($slotDuration);

                    // Check if slot is booked by a CONFIRMED booking
                    $confirmedBooking = $confirmedBookings->first(function ($booking) use ($slotTime, $slotEnd) {
                        // Check if the slot overlaps with a confirmed booking
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
                            'is_booked' => (bool) $confirmedBooking, // Only confirmed bookings mark slot as booked
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
        // Only consider confirmed bookings as "booked" - pending bookings should still allow new reservations
        $confirmedBookings = $prestataire->bookings()->where('status', 'confirmed')->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();
        // Get all bookings (including pending) for display purposes
        $allBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();

        // Check if this is an hourly service with a specific duration
        $isHourlyWithDuration = $service->price_type === 'heure' && $service->quantity;
        $serviceDurationMinutes = $isHourlyWithDuration ? $service->quantity * 60 : 60; // Default to 60 minutes

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Utiliser dayOfWeek (0=dimanche, 1=lundi, etc.) au lieu de dayOfWeekIso
            $dayOfWeek = $date->dayOfWeek;
            $availability = $availabilities->firstWhere('day_of_week', $dayOfWeek);

            if ($availability) {
                $sTime = Carbon::parse($availability->start_time);
                $eTime = Carbon::parse($availability->end_time);
                $startTime = $date->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                $endTime = $date->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                
                // For hourly services with specific duration, we need to create slots that can accommodate the full service duration
                if ($isHourlyWithDuration) {
                    // Calculate total available time (excluding break time)
                    $totalWorkMinutes = $startTime->diffInMinutes($endTime);
                    
                    if ($availability->break_start_time && $availability->break_end_time) {
                        $breakStartTime = Carbon::parse($availability->break_start_time);
                        $breakEndTime = Carbon::parse($availability->break_end_time);
                        $breakStart = $date->copy()->hour($breakStartTime->hour)->minute($breakStartTime->minute)->second($breakStartTime->second);
                        $breakEnd = $date->copy()->hour($breakEndTime->hour)->minute($breakEndTime->minute)->second($breakEndTime->second);
                        $breakDuration = $breakStart->diffInMinutes($breakEnd);
                        $totalWorkMinutes -= $breakDuration;
                    }
                    
                    // Check if the service can fit in the total available time
                    if ($totalWorkMinutes >= $serviceDurationMinutes) {
                        // Create slots that represent the full service duration
                        $slotDuration = $serviceDurationMinutes;
                        
                        // Generate slots based on the service duration
                        for ($slotTime = $startTime->copy(); $slotTime->lt($endTime); $slotTime->addMinutes($slotDuration)) {
                            $slotEnd = $slotTime->copy()->addMinutes($slotDuration);
                            
                            // If there's a break and the service would overlap it, adjust the end time
                            if ($availability->break_start_time && $availability->break_end_time) {
                                $breakStartTime = Carbon::parse($availability->break_start_time);
                                $breakEndTime = Carbon::parse($availability->break_end_time);
                                $breakStart = $date->copy()->hour($breakStartTime->hour)->minute($breakStartTime->minute)->second($breakStartTime->second);
                                $breakEnd = $date->copy()->hour($breakEndTime->hour)->minute($breakEndTime->minute)->second($breakEndTime->second);
                                
                                // If the service would overlap with the break
                                if ($slotTime < $breakEnd && $slotEnd > $breakStart) {
                                    // Extend the service end time by the break duration
                                    $slotEnd->addMinutes($breakEnd->diffInMinutes($breakStart));
                                }
                            }
                            
                            // Ensure the slot doesn't extend beyond the working day
                            if ($slotEnd->lte($endTime)) {
                                // Check if slot is booked by a CONFIRMED booking
                                $confirmedBooking = $confirmedBookings->first(function ($booking) use ($slotTime, $slotEnd) {
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
                                    'is_booked' => (bool) $confirmedBooking,
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

                        // Check if slot is booked by a CONFIRMED booking
                        $confirmedBooking = $confirmedBookings->first(function ($booking) use ($slotTime, $slotEnd) {
                            // Check if the slot overlaps with a confirmed booking
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
                            'is_booked' => (bool) $confirmedBooking, // Only confirmed bookings mark slot as booked
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
        // Only consider confirmed bookings as "booked" - pending bookings should still allow new reservations
        $confirmedBookings = $prestataire->bookings()->where('status', 'confirmed')->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();
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
                    
                    // Check for confirmed bookings that overlap with this day
                    $dayBooked = $confirmedBookings->first(function ($booking) use ($checkStart, $checkEnd) {
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