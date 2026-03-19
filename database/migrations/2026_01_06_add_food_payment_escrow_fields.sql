-- ============================================================================
-- MIGRATION SQL - Système d'acompte/escrow pour commandes food
-- À copier-coller dans phpMyAdmin
-- ============================================================================

-- 1) Ajouter les champs de politique de paiement sur les produits food
ALTER TABLE `food_products` 
ADD COLUMN `payment_policy` ENUM('cash', 'deposit', 'full_prepay') NOT NULL DEFAULT 'cash' COMMENT 'cash=espèces, deposit=acompte, full_prepay=paiement total' AFTER `is_available`,
ADD COLUMN `deposit_percent` TINYINT UNSIGNED NULL DEFAULT 30 COMMENT 'Pourcentage acompte (si deposit)' AFTER `payment_policy`;

-- 2) Ajouter les champs escrow/blocage sur les commandes food
ALTER TABLE `food_orders`
ADD COLUMN `escrow_status` ENUM('none', 'held', 'released', 'refunded', 'partial_refund') NOT NULL DEFAULT 'none' COMMENT 'Statut du blocage financier' AFTER `payment_status`,
ADD COLUMN `amount_held` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Montant bloqué (acompte ou total)' AFTER `escrow_status`,
ADD COLUMN `amount_released` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Montant libéré au vendeur' AFTER `amount_held`,
ADD COLUMN `amount_refunded` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Montant remboursé au client' AFTER `amount_released`,
ADD COLUMN `held_at` DATETIME NULL DEFAULT NULL COMMENT 'Date de blocage du paiement' AFTER `amount_refunded`,
ADD COLUMN `released_at` DATETIME NULL DEFAULT NULL COMMENT 'Date de libération au vendeur' AFTER `held_at`,
ADD COLUMN `refunded_at` DATETIME NULL DEFAULT NULL COMMENT 'Date de remboursement' AFTER `released_at`,
ADD COLUMN `code_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Nombre de tentatives de code' AFTER `delivery_code`,
ADD COLUMN `code_locked_until` DATETIME NULL DEFAULT NULL COMMENT 'Verrouillage temporaire après trop de tentatives' AFTER `code_attempts`,
ADD COLUMN `code_expires_at` DATETIME NULL DEFAULT NULL COMMENT 'Expiration du code (24h après ready)' AFTER `code_locked_until`,
ADD COLUMN `refund_reason` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Raison du remboursement' AFTER `refunded_at`;

-- 3) Index pour les jobs de timeout
ALTER TABLE `food_orders`
ADD INDEX `idx_food_orders_escrow_timeout` (`escrow_status`, `code_expires_at`, `code_verified_at`);

-- 4) Table de log des tentatives de code (anti brute-force)
CREATE TABLE IF NOT EXISTS `food_order_code_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `food_order_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NULL COMMENT 'Qui a tenté (prestataire)',
    `ip_address` VARCHAR(45) NULL,
    `code_entered` VARCHAR(10) NULL,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_code_attempts_order` (`food_order_id`),
    INDEX `idx_code_attempts_ip` (`ip_address`, `created_at`),
    FOREIGN KEY (`food_order_id`) REFERENCES `food_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- FIN DE LA MIGRATION
-- ============================================================================
