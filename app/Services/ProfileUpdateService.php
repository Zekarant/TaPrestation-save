<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProfileUpdateService
{
    /**
     * Met à jour le nom et l'email de l'utilisateur.
     * Force la re-vérification si l'email change.
     */
    public function updateUserIdentity(User $user, string $name, string $email): void
    {
        $user->name = $name;

        if ($user->email !== $email) {
            $user->email = $email;
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->email_verified_at === null && $user->wasChanged('email')) {
            $user->sendEmailVerificationNotification();
        }
    }

    /**
     * Gère l'upload d'une photo de profil.
     * Supprime l'ancienne photo et stocke la nouvelle.
     *
     * @return string|null Le chemin de la nouvelle photo, ou null si pas d'upload
     */
    public function handlePhotoUpload($request, ?string $currentPhoto, string $storagePath): ?string
    {
        if (!$request->hasFile('photo')) {
            return null;
        }

        if ($currentPhoto && Storage::disk('public')->exists($currentPhoto)) {
            Storage::disk('public')->delete($currentPhoto);
        }

        return $request->file('photo')->store($storagePath, 'public');
    }
}
