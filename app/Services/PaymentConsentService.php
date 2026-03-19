<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Support\TableExistenceCache;
/**
 * Service de gestion du consentement aux conditions de paiement
 * 
 * RGPD: Enregistre l'acceptation des conditions avec horodatage, IP, user agent et version.
 */
class PaymentConsentService
{
    /**
     * Enregistrer le consentement d'un acheteur
     */
    public function recordConsent(
        int $userId,
        string $consentableType,
        int $consentableId,
        Request $request,
        string $version = 'v1.0',
        string $consentType = 'payment_terms',
        ?array $metadata = null
    ): bool {
        try {
            $now = now();
            $ip = $request->ip();
            $userAgent = $request->userAgent();
            
            // Calculer le hash des conditions (pour preuve d'intégrité)
            $termsHash = $this->getTermsHash($consentType, $version);

            // Enregistrer dans la table centralisée payment_consents
            if (TableExistenceCache::has('payment_consents')) {
                DB::table('payment_consents')->insert([
                    'user_id' => $userId,
                    'consentable_type' => $consentableType,
                    'consentable_id' => $consentableId,
                    'consent_type' => $consentType,
                    'version' => $version,
                    'terms_hash' => $termsHash,
                    'ip_address' => $ip ? Crypt::encryptString($ip) : null,
                    'user_agent' => $userAgent ? Crypt::encryptString($userAgent) : null,
                    'metadata' => $metadata ? json_encode($metadata) : null,
                    'consented_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Mettre à jour la table de l'objet concerné
            $this->updateConsentableRecord($consentableType, $consentableId, $now, $ip, $userAgent, $version);

            Log::info("Consent recorded: user #{$userId} for {$consentableType}#{$consentableId} (v{$version})");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to record consent: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enregistrer le consentement pour plusieurs objets (panier)
     */
    public function recordConsentForCart(
        int $userId,
        array $items,
        Request $request,
        string $version = 'v1.0'
    ): int {
        $count = 0;

        foreach ($items as $item) {
            $purchasable = $item->purchasable ?? null;
            if (!$purchasable) continue;

            $type = get_class($purchasable);
            $id = $purchasable->id;

            if ($this->recordConsent($userId, $type, $id, $request, $version)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Mettre à jour les colonnes de consentement sur l'objet lié
     */
    private function updateConsentableRecord(
        string $type,
        int $id,
        $timestamp,
        string $ip,
        ?string $userAgent,
        string $version
    ): void {
        $tableName = $this->getTableName($type);
        if (!$tableName || !TableExistenceCache::has($tableName)) {
            return;
        }

        $updates = ['updated_at' => $timestamp];

        if (Schema::hasColumn($tableName, 'buyer_consent_at')) {
            $updates['buyer_consent_at'] = $timestamp;
        }
        if (Schema::hasColumn($tableName, 'buyer_consent_ip')) {
            $updates['buyer_consent_ip'] = $ip;
        }
        if (Schema::hasColumn($tableName, 'buyer_consent_user_agent')) {
            $updates['buyer_consent_user_agent'] = $userAgent;
        }
        if (Schema::hasColumn($tableName, 'buyer_consent_version')) {
            $updates['buyer_consent_version'] = $version;
        }

        if (count($updates) > 1) {
            DB::table($tableName)->where('id', $id)->update($updates);
        }
    }

    /**
     * Obtenir le nom de table à partir du type de modèle
     */
    private function getTableName(string $type): ?string
    {
        if (str_contains($type, 'Booking')) {
            return 'bookings';
        }
        if (str_contains($type, 'EquipmentRentalRequest')) {
            return 'equipment_rental_requests';
        }
        if (str_contains($type, 'UrgentSalePurchase')) {
            return 'urgent_sale_purchases';
        }
        if (str_contains($type, 'UrgentSale')) {
            // Pour les achats, on enregistre sur urgent_sale_purchases après création
            return null;
        }
        return null;
    }

    /**
     * Générer un hash des conditions pour preuve d'intégrité
     */
    private function getTermsHash(string $consentType, string $version): string
    {
        $termsContent = $this->getTermsContent($consentType, $version);
        return hash('sha256', $termsContent);
    }

    /**
     * Obtenir le contenu des conditions (pour hash et affichage)
     */
    public function getTermsContent(string $consentType, string $version): string
    {
        // Conditions de paiement sécurisé v1.0
        if ($consentType === 'payment_terms' && $version === 'v1.0') {
            return <<<'TERMS'
CONDITIONS DE PAIEMENT SÉCURISÉ (ESCROW) - Version 1.0

=== SERVICES ===
- Paiement bloqué jusqu'à la réalisation du service
- 48h pour confirmer après le service
- Sans action : libération automatique au prestataire
- Service non réalisé : remboursement total automatique
- Annulation dans les délais : selon conditions du prestataire
- Annulation hors délai : aucun remboursement

=== LOCATION D'ÉQUIPEMENT ===
- Location + caution bloqués jusqu'au retour
- Retour en bon état : caution remboursée
- Équipement endommagé : caution retenue (tout ou partie)
- Annulation dans les délais : remboursement total
- Annulation hors délai : selon conditions du prestataire

=== VENTES / ANNONCES ===
- Paiement bloqué sur la plateforme (escrow)
- 48h après livraison pour confirmer la conformité
- Sans action : paiement libéré au vendeur
- Non-conformité : litige ouvert automatiquement
- Sans accord sous 7 jours : partage 40% client / 60% vendeur
- Retour reçu par vendeur : remboursement total
- Litige/remboursement : commission rendue
- Pas d'annulation après paiement

=== COMMISSION ===
- Commission prélevée sur chaque transaction
- Rendue en cas de litige ou remboursement
TERMS;
        }

        return "Conditions version {$version} pour {$consentType}";
    }

    /**
     * Vérifier si un utilisateur a accepté les conditions pour un objet
     */
    public function hasConsent(int $userId, string $consentableType, int $consentableId): bool
    {
        if (!TableExistenceCache::has('payment_consents')) {
            return false;
        }

        return DB::table('payment_consents')
            ->where('user_id', $userId)
            ->where('consentable_type', $consentableType)
            ->where('consentable_id', $consentableId)
            ->exists();
    }
}
