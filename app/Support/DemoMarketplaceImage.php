<?php

namespace App\Support;

final class DemoMarketplaceImage
{
    public static function normalizePath(?string $path, array $sameHosts = [], bool $preferRenderedDemo = false): ?string
    {
        if (!is_string($path)) {
            return null;
        }

        $current = trim($path);
        if ($current === '') {
            return null;
        }

        if (preg_match('/^data:/i', $current)) {
            return $current;
        }

        $hosts = self::sameOriginHosts($sameHosts);

        for ($i = 0; $i < 6; $i++) {
            $isAbsoluteUrl = preg_match('/^https?:\/\//i', $current) === 1;
            $parts = parse_url($current);
            $pathPart = (string) ($parts['path'] ?? '');
            $queryPart = (string) ($parts['query'] ?? '');

            if ($isAbsoluteUrl) {
                $host = strtolower((string) ($parts['host'] ?? ''));

                if ($host !== '' && !in_array($host, $hosts, true)) {
                    return $current;
                }
            }

            if (self::isServeImagePath($pathPart)) {
                parse_str($queryPart, $queryParams);
                $nested = $queryParams['path'] ?? null;

                if (is_string($nested) && $nested !== '') {
                    $current = $nested;
                    continue;
                }
            }

            if (self::isServeFoodImagePath($pathPart)) {
                parse_str($queryPart, $queryParams);
                $nested = $queryParams['path'] ?? ($queryParams['file'] ?? null);

                if (is_string($nested) && $nested !== '') {
                    $current = $nested;
                    continue;
                }
            }

            if ($pathPart !== '' && str_contains($pathPart, '/storage/')) {
                $current = substr($pathPart, strpos($pathPart, '/storage/') + strlen('/storage/'));
                break;
            }

            break;
        }

        $cleanPath = ltrim(str_replace(['\\', '//'], '/', $current), '/');

        foreach (['public/storage/', 'storage/'] as $prefix) {
            if (str_starts_with($cleanPath, $prefix)) {
                $cleanPath = substr($cleanPath, strlen($prefix));
                break;
            }
        }

        if ($cleanPath === '') {
            return null;
        }

        if ($preferRenderedDemo && str_starts_with($cleanPath, 'demo-marketplace/')) {
            return self::renderedPath($cleanPath) ?? $cleanPath;
        }

        return $cleanPath;
    }

    public static function renderedPath(?string $path): ?string
    {
        $cleanPath = self::normalizePath($path, [], false);

        if (!$cleanPath || !str_starts_with($cleanPath, 'demo-marketplace/')) {
            return $cleanPath;
        }

        if (str_starts_with($cleanPath, 'demo-marketplace/rendered/')) {
            return $cleanPath;
        }

        if (!preg_match('/\.svg$/i', $cleanPath)) {
            return $cleanPath;
        }

        $renderedPath = preg_replace('/^demo-marketplace\//', 'demo-marketplace/rendered/', $cleanPath);

        return preg_replace('/\.svg$/i', '.png', $renderedPath);
    }

    public static function ensureRenderedPng(?string $path, string $storageRoot, bool $force = false): ?string
    {
        $cleanPath = self::normalizePath($path, [], false);

        if (!$cleanPath || !str_starts_with($cleanPath, 'demo-marketplace/')) {
            return $cleanPath;
        }

        if (!preg_match('/\.svg$/i', $cleanPath)) {
            return $cleanPath;
        }

        $renderedPath = self::renderedPath($cleanPath);
        if (!$renderedPath) {
            return $cleanPath;
        }

        $targetPath = rtrim($storageRoot, '/\\') . '/' . str_replace('\\', '/', $renderedPath);

        if (!$force && is_file($targetPath)) {
            return $renderedPath;
        }

        $directory = dirname($targetPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        self::renderPngFromPath($cleanPath, $targetPath);

        return $renderedPath;
    }

    public static function mimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };
    }

    public static function sameOriginHosts(array $extraHosts = []): array
    {
        $hosts = ['taprestation.com', 'www.taprestation.com'];

        foreach ($extraHosts as $host) {
            $cleanHost = strtolower(trim((string) $host));
            if ($cleanHost !== '') {
                $hosts[] = $cleanHost;
            }
        }

        return array_values(array_unique($hosts));
    }

    public static function renderPngFromPath(string $relativePath, string $targetPath): void
    {
        $scene = self::sceneForPath($relativePath);
        $isAvatar = $scene['kind'] === 'avatar';
        $width = $isAvatar ? 640 : 1280;
        $height = $isAvatar ? 640 : 960;

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);
        imagesavealpha($image, false);

        self::fillVerticalGradient(
            $image,
            $width,
            $height,
            self::rgb($scene['palette']['surface']),
            self::rgb($scene['palette']['surface_alt'])
        );

        $panel = self::allocate($image, $scene['palette']['panel']);
        $accent = self::allocate($image, $scene['palette']['accent']);
        $accentSoft = self::allocate($image, $scene['palette']['accent_soft']);
        $textDark = self::allocate($image, $scene['palette']['text_dark']);
        $textMuted = self::allocate($image, $scene['palette']['text_muted']);
        $white = self::allocate($image, '#ffffff');

        if ($isAvatar) {
            imagefilledrectangle($image, 44, 44, $width - 44, $height - 44, $panel);
            imagefilledellipse($image, (int) ($width / 2), 220, 220, 220, $accent);
            imagefilledellipse($image, (int) ($width / 2), 470, 300, 240, $accentSoft);
            imagestring(
                $image,
                5,
                self::centerText($width, $scene['initials'], 5),
                204,
                $scene['initials'],
                $white
            );
            imagestring(
                $image,
                4,
                self::centerText($width, $scene['theme_label'], 4),
                420,
                $scene['theme_label'],
                $white
            );
            imagestring(
                $image,
                3,
                self::centerText($width, 'PROFIL DEMO', 3),
                500,
                'PROFIL DEMO',
                $white
            );
        } else {
            imagefilledrectangle($image, 64, 64, $width - 64, $height - 64, $white);
            imagefilledrectangle($image, 64, 64, 540, $height - 64, $panel);
            imagefilledrectangle($image, 90, 100, 514, 524, $accentSoft);
            imagefilledellipse($image, 302, 312, 320, 320, $accent);
            imagefilledellipse($image, 1060, 180, 180, 180, $accentSoft);
            imagefilledellipse($image, 1060, 740, 220, 220, $accentSoft);
            imagefilledrectangle($image, 590, 120, 1120, 182, $accent);
            imagefilledrectangle($image, 590, 642, 1120, 804, $scene['palette']['chip_color'] !== null ? self::allocate($image, $scene['palette']['chip_color']) : $accentSoft);

            self::drawIcon($image, $scene['icon'], 150, 160, 304, $white, $textDark);

            imagestring($image, 5, 618, 140, $scene['badge'], $white);
            imagestring($image, 4, 618, 664, $scene['theme_label'], $textDark);
            imagestring($image, 3, 618, 704, $scene['subtitle'], $textDark);

            $titleLines = self::wrapTitle($scene['title'], 30, 2);
            $titleY = 280;
            foreach ($titleLines as $line) {
                imagestring($image, 5, 618, $titleY, $line, $textDark);
                $titleY += 34;
            }

            imagestring($image, 3, 618, 388, 'Illustration demo adaptee a l annonce', $textMuted);
        }

        imagepng($image, $targetPath, 6);
        imagedestroy($image);
    }

    private static function isServeImagePath(string $path): bool
    {
        return $path === 'serve-image.php' || str_ends_with($path, '/serve-image.php');
    }

    private static function isServeFoodImagePath(string $path): bool
    {
        return $path === 'serve-food-image.php' || str_ends_with($path, '/serve-food-image.php');
    }

    private static function sceneForPath(string $relativePath): array
    {
        $normalized = str_replace('\\', '/', $relativePath);
        $slug = strtolower((string) preg_replace('/\.(svg|png|jpg|jpeg|webp)$/i', '', basename($normalized)));
        $title = self::humanizeTitle($slug);

        if (str_contains($normalized, '/avatars/')) {
            return [
                'kind' => 'avatar',
                'initials' => self::initialsFromSlug($slug),
                'theme_label' => 'FRANCE',
                'palette' => [
                    'surface' => '#f8fafc',
                    'surface_alt' => '#e2e8f0',
                    'panel' => '#0f172a',
                    'accent' => '#f97316',
                    'accent_soft' => '#1d4ed8',
                    'text_dark' => '#0f172a',
                    'text_muted' => '#475569',
                    'chip_color' => null,
                ],
            ];
        }

        $kind = self::kindFromSlug($slug);
        $theme = self::themeForSlug($kind, $slug);

        return [
            'kind' => $kind,
            'badge' => match ($kind) {
                'service' => 'SERVICE',
                'equipment' => 'EQUIPEMENT',
                'urgent_sale' => 'VENTE URGENTE',
                'food' => 'FOOD',
                default => 'ANNONCE',
            },
            'theme_label' => $theme['label'],
            'subtitle' => $theme['subtitle'],
            'title' => strtoupper($title),
            'icon' => $theme['icon'],
            'palette' => $theme['palette'],
        ];
    }

    private static function kindFromSlug(string $slug): string
    {
        return match (true) {
            str_starts_with($slug, 'service-') => 'service',
            str_starts_with($slug, 'equipment-') => 'equipment',
            str_starts_with($slug, 'urgent-sale-') => 'urgent_sale',
            str_starts_with($slug, 'food-') => 'food',
            default => 'listing',
        };
    }

    private static function themeForSlug(string $kind, string $slug): array
    {
        $rules = match ($kind) {
            'service' => [
                ['keywords' => ['plomb', 'fuite', 'chauffe', 'eau', 'canalis'], 'label' => 'PLOMBERIE', 'subtitle' => 'Intervention et depannage', 'icon' => 'tools', 'palette' => self::palette('#0f766e', '#14b8a6', '#99f6e4', '#ecfeff')],
                ['keywords' => ['elect', 'tableau', 'circuit', 'prise'], 'label' => 'ELECTRICITE', 'subtitle' => 'Installation et securite', 'icon' => 'bolt', 'palette' => self::palette('#1d4ed8', '#60a5fa', '#bfdbfe', '#eff6ff')],
                ['keywords' => ['jardin', 'haie', 'tonte', 'elag'], 'label' => 'JARDIN', 'subtitle' => 'Exterieur et entretien', 'icon' => 'leaf', 'palette' => self::palette('#166534', '#4ade80', '#bbf7d0', '#f0fdf4')],
                ['keywords' => ['nettoyage', 'vitre', 'chantier'], 'label' => 'NETTOYAGE', 'subtitle' => 'Proprete et remise en etat', 'icon' => 'spray', 'palette' => self::palette('#0f766e', '#22d3ee', '#a5f3fc', '#ecfeff')],
                ['keywords' => ['photo', 'shoot', 'studio', 'reportage'], 'label' => 'PHOTO', 'subtitle' => 'Prises de vue et studio', 'icon' => 'camera', 'palette' => self::palette('#7c3aed', '#c084fc', '#ddd6fe', '#faf5ff')],
                ['keywords' => ['site', 'wordpress', 'web', 'maintenance', 'performance'], 'label' => 'WEB', 'subtitle' => 'Sites et support digital', 'icon' => 'laptop', 'palette' => self::palette('#0f172a', '#38bdf8', '#bae6fd', '#f8fafc')],
            ],
            'equipment' => [
                ['keywords' => ['perceuse', 'bosch', 'makita', 'scie', 'echafaudage', 'ponceuse'], 'label' => 'BRICOLAGE', 'subtitle' => 'Materiel chantier', 'icon' => 'drill', 'palette' => self::palette('#7c2d12', '#fb923c', '#fed7aa', '#fff7ed')],
                ['keywords' => ['tondeuse', 'tronconneuse', 'taille', 'souffleur'], 'label' => 'JARDIN', 'subtitle' => 'Materiel exterieur', 'icon' => 'leaf', 'palette' => self::palette('#166534', '#4ade80', '#bbf7d0', '#f0fdf4')],
                ['keywords' => ['canon', 'sony', 'objectif', 'drone', 'gopro'], 'label' => 'PHOTO', 'subtitle' => 'Capture et audiovisuel', 'icon' => 'camera', 'palette' => self::palette('#312e81', '#818cf8', '#c7d2fe', '#eef2ff')],
                ['keywords' => ['macbook', 'iphone', 'ordinateur', 'laptop', 'pc', 'samsung', 'televiseur'], 'label' => 'HIGH-TECH', 'subtitle' => 'Informatique et ecran', 'icon' => 'laptop', 'palette' => self::palette('#0f172a', '#38bdf8', '#bae6fd', '#f8fafc')],
            ],
            'urgent_sale' => [
                ['keywords' => ['perceuse', 'bosch', 'makita', 'scie'], 'label' => 'OUTILLAGE', 'subtitle' => 'Bon etat et vente rapide', 'icon' => 'drill', 'palette' => self::palette('#7c2d12', '#f97316', '#fed7aa', '#fff7ed')],
                ['keywords' => ['canon', 'sony', 'objectif', 'drone'], 'label' => 'PHOTO', 'subtitle' => 'Materiel disponible maintenant', 'icon' => 'camera', 'palette' => self::palette('#312e81', '#a78bfa', '#ddd6fe', '#f5f3ff')],
                ['keywords' => ['iphone', 'macbook', 'samsung', 'televiseur', 'console'], 'label' => 'HIGH-TECH', 'subtitle' => 'Produit controle et pret a partir', 'icon' => 'phone', 'palette' => self::palette('#0f172a', '#38bdf8', '#bae6fd', '#f8fafc')],
                ['keywords' => ['canape', 'table', 'chaise', 'mobilier'], 'label' => 'MAISON', 'subtitle' => 'Retrait rapide', 'icon' => 'home', 'palette' => self::palette('#78350f', '#f59e0b', '#fde68a', '#fffbeb')],
            ],
            'food' => [
                ['keywords' => ['pizza', 'regina', 'margherita', 'calzone'], 'label' => 'PIZZA', 'subtitle' => 'Cuisine chaude et genereuse', 'icon' => 'pizza', 'palette' => self::palette('#9a3412', '#fb923c', '#fed7aa', '#fff7ed')],
                ['keywords' => ['burger', 'cheese'], 'label' => 'BURGER', 'subtitle' => 'Recette maison', 'icon' => 'burger', 'palette' => self::palette('#92400e', '#fbbf24', '#fde68a', '#fffbeb')],
                ['keywords' => ['eclair', 'dessert', 'gateau', 'cookie', 'tarte', 'mousse'], 'label' => 'DESSERT', 'subtitle' => 'Sucre et gourmandise', 'icon' => 'cake', 'palette' => self::palette('#be185d', '#f472b6', '#fbcfe8', '#fdf2f8')],
                ['keywords' => ['jus', 'citronnade', 'smoothie', 'boisson', 'cafe', 'the'], 'label' => 'BOISSON', 'subtitle' => 'Fraicheur et saveur', 'icon' => 'drink', 'palette' => self::palette('#0f766e', '#2dd4bf', '#99f6e4', '#f0fdfa')],
                ['keywords' => ['salade', 'poke'], 'label' => 'SALADE', 'subtitle' => 'Fraicheur et equilibre', 'icon' => 'bowl', 'palette' => self::palette('#166534', '#4ade80', '#bbf7d0', '#f0fdf4')],
                ['keywords' => ['sandwich', 'panini', 'tacos', 'wrap', 'quiche'], 'label' => 'SNACK', 'subtitle' => 'Pret a emporter', 'icon' => 'sandwich', 'palette' => self::palette('#7c2d12', '#fb7185', '#fecdd3', '#fff1f2')],
            ],
            default => [],
        };

        foreach ($rules as $rule) {
            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($slug, $keyword)) {
                    return $rule;
                }
            }
        }

        return match ($kind) {
            'service' => ['label' => 'SERVICE', 'subtitle' => 'Annonce de prestation', 'icon' => 'tools', 'palette' => self::palette('#1d4ed8', '#60a5fa', '#bfdbfe', '#eff6ff')],
            'equipment' => ['label' => 'MATERIEL', 'subtitle' => 'Location disponible', 'icon' => 'box', 'palette' => self::palette('#166534', '#4ade80', '#bbf7d0', '#f0fdf4')],
            'urgent_sale' => ['label' => 'VENTE', 'subtitle' => 'Prix direct vendeur', 'icon' => 'bolt', 'palette' => self::palette('#991b1b', '#fb7185', '#fecdd3', '#fff1f2')],
            'food' => ['label' => 'CUISINE', 'subtitle' => 'Produit du jour', 'icon' => 'plate', 'palette' => self::palette('#9a3412', '#fb923c', '#fed7aa', '#fff7ed')],
            default => ['label' => 'ANNONCE', 'subtitle' => 'Illustration demo', 'icon' => 'box', 'palette' => self::palette('#334155', '#94a3b8', '#cbd5e1', '#f8fafc')],
        };
    }

    private static function palette(string $panel, string $accent, string $accentSoft, string $surface): array
    {
        return [
            'panel' => $panel,
            'accent' => $accent,
            'accent_soft' => $accentSoft,
            'surface' => $surface,
            'surface_alt' => self::mixHex($surface, '#ffffff', 0.55),
            'text_dark' => '#0f172a',
            'text_muted' => '#475569',
            'chip_color' => '#ffffff',
        ];
    }

    private static function humanizeTitle(string $slug): string
    {
        $clean = preg_replace('/^(service|equipment|urgent-sale|food)-/i', '', $slug);
        $clean = preg_replace('/-\d+$/', '', (string) $clean);
        $clean = str_replace('-', ' ', (string) $clean);
        $clean = trim((string) $clean);

        return $clean !== '' ? ucwords($clean) : 'Annonce demo';
    }

    private static function initialsFromSlug(string $slug): string
    {
        $parts = array_values(array_filter(explode('-', $slug)));
        $letters = [];

        foreach ($parts as $part) {
            if (ctype_digit($part)) {
                continue;
            }

            $letters[] = strtoupper(substr($part, 0, 1));
            if (count($letters) === 2) {
                break;
            }
        }

        return implode('', $letters) ?: 'TP';
    }

    private static function drawIcon($image, string $icon, int $x, int $y, int $size, int $primary, int $secondary): void
    {
        switch ($icon) {
            case 'camera':
                imagefilledrectangle($image, $x + 34, $y + 74, $x + $size - 34, $y + $size - 70, $primary);
                imagefilledellipse($image, $x + (int) ($size / 2), $y + (int) ($size / 2) + 10, 126, 126, $secondary);
                imagefilledellipse($image, $x + (int) ($size / 2), $y + (int) ($size / 2) + 10, 86, 86, $primary);
                imagefilledrectangle($image, $x + 78, $y + 40, $x + 168, $y + 88, $primary);
                break;

            case 'laptop':
                imagefilledrectangle($image, $x + 52, $y + 52, $x + $size - 52, $y + $size - 112, $primary);
                imagefilledrectangle($image, $x + 84, $y + 84, $x + $size - 84, $y + $size - 144, $secondary);
                imagefilledpolygon($image, [
                    $x + 28, $y + $size - 98,
                    $x + $size - 28, $y + $size - 98,
                    $x + $size - 6, $y + $size - 52,
                    $x + 6, $y + $size - 52,
                ], 4, $primary);
                break;

            case 'phone':
                imagefilledrectangle($image, $x + 100, $y + 28, $x + $size - 100, $y + $size - 28, $primary);
                imagefilledrectangle($image, $x + 126, $y + 72, $x + $size - 126, $y + $size - 92, $secondary);
                imagefilledellipse($image, $x + (int) ($size / 2), $y + $size - 58, 20, 20, $secondary);
                break;

            case 'pizza':
                imagefilledellipse($image, $x + (int) ($size / 2), $y + (int) ($size / 2), 248, 248, $primary);
                imagefilledellipse($image, $x + (int) ($size / 2), $y + (int) ($size / 2), 210, 210, $secondary);
                imageline($image, $x + (int) ($size / 2), $y + (int) ($size / 2), $x + (int) ($size / 2), $y + 50, $primary);
                imageline($image, $x + (int) ($size / 2), $y + (int) ($size / 2), $x + 88, $y + 118, $primary);
                imageline($image, $x + (int) ($size / 2), $y + (int) ($size / 2), $x + $size - 88, $y + 118, $primary);
                imagefilledellipse($image, $x + 150, $y + 164, 28, 28, $primary);
                imagefilledellipse($image, $x + 236, $y + 114, 28, 28, $primary);
                imagefilledellipse($image, $x + 322, $y + 176, 28, 28, $primary);
                break;

            case 'burger':
                imagefilledellipse($image, $x + (int) ($size / 2), $y + 132, 248, 118, $primary);
                imagefilledrectangle($image, $x + 92, $y + 162, $x + $size - 92, $y + 190, $secondary);
                imagefilledrectangle($image, $x + 92, $y + 198, $x + $size - 92, $y + 232, $primary);
                imagefilledrectangle($image, $x + 92, $y + 238, $x + $size - 92, $y + 254, $secondary);
                imagefilledellipse($image, $x + (int) ($size / 2), $y + 286, 248, 86, $primary);
                break;

            case 'cake':
                imagefilledrectangle($image, $x + 88, $y + 150, $x + $size - 88, $y + 250, $primary);
                imagefilledrectangle($image, $x + 112, $y + 114, $x + $size - 112, $y + 150, $secondary);
                imagefilledrectangle($image, $x + (int) ($size / 2) - 10, $y + 60, $x + (int) ($size / 2) + 10, $y + 114, $primary);
                imagefilledellipse($image, $x + (int) ($size / 2), $y + 48, 26, 36, $secondary);
                break;

            case 'drink':
                imagefilledpolygon($image, [
                    $x + 132, $y + 82,
                    $x + $size - 132, $y + 82,
                    $x + $size - 164, $y + $size - 56,
                    $x + 164, $y + $size - 56,
                ], 4, $primary);
                imageline($image, $x + 246, $y + 40, $x + 286, $y + 126, $secondary);
                imageline($image, $x + 286, $y + 126, $x + 314, $y + 126, $secondary);
                break;

            case 'sandwich':
                imagefilledpolygon($image, [
                    $x + 108, $y + 124,
                    $x + $size - 108, $y + 124,
                    $x + $size - 144, $y + 186,
                    $x + 144, $y + 186,
                ], 4, $primary);
                imagefilledrectangle($image, $x + 120, $y + 186, $x + $size - 120, $y + 214, $secondary);
                imagefilledpolygon($image, [
                    $x + 108, $y + 214,
                    $x + $size - 108, $y + 214,
                    $x + $size - 144, $y + 276,
                    $x + 144, $y + 276,
                ], 4, $primary);
                break;

            case 'bowl':
                imagefilledellipse($image, $x + (int) ($size / 2), $y + 218, 230, 116, $primary);
                imagefilledellipse($image, $x + (int) ($size / 2), $y + 198, 184, 78, $secondary);
                imagefilledellipse($image, $x + 170, $y + 158, 54, 54, $primary);
                imagefilledellipse($image, $x + 240, $y + 138, 54, 54, $primary);
                imagefilledellipse($image, $x + 308, $y + 160, 54, 54, $primary);
                break;

            case 'leaf':
                imagefilledpolygon($image, [
                    $x + 214, $y + 76,
                    $x + 338, $y + 176,
                    $x + 254, $y + 310,
                    $x + 114, $y + 226,
                ], 4, $primary);
                imageline($image, $x + 214, $y + 96, $x + 214, $y + 300, $secondary);
                imageline($image, $x + 214, $y + 170, $x + 292, $y + 138, $secondary);
                imageline($image, $x + 214, $y + 208, $x + 284, $y + 220, $secondary);
                imageline($image, $x + 214, $y + 244, $x + 154, $y + 208, $secondary);
                break;

            case 'spray':
                imagefilledrectangle($image, $x + 148, $y + 122, $x + 284, $y + 316, $primary);
                imagefilledrectangle($image, $x + 216, $y + 82, $x + 320, $y + 132, $primary);
                imagefilledrectangle($image, $x + 284, $y + 104, $x + 356, $y + 124, $secondary);
                imagefilledellipse($image, $x + 372, $y + 108, 22, 22, $secondary);
                break;

            case 'bolt':
                imagefilledpolygon($image, [
                    $x + 206, $y + 54,
                    $x + 132, $y + 206,
                    $x + 214, $y + 206,
                    $x + 170, $y + 334,
                    $x + 318, $y + 162,
                    $x + 230, $y + 162,
                ], 6, $primary);
                break;

            case 'drill':
                imagefilledpolygon($image, [
                    $x + 92, $y + 124,
                    $x + 292, $y + 124,
                    $x + 348, $y + 164,
                    $x + 348, $y + 236,
                    $x + 224, $y + 236,
                    $x + 224, $y + 314,
                    $x + 160, $y + 314,
                    $x + 148, $y + 236,
                    $x + 92, $y + 236,
                ], 9, $primary);
                imagefilledrectangle($image, $x + 348, $y + 174, $x + $size - 34, $y + 194, $secondary);
                break;

            case 'home':
                imagefilledpolygon($image, [
                    $x + 102, $y + 186,
                    $x + 214, $y + 82,
                    $x + 326, $y + 186,
                ], 3, $primary);
                imagefilledrectangle($image, $x + 138, $y + 186, $x + 290, $y + 316, $primary);
                imagefilledrectangle($image, $x + 198, $y + 238, $x + 230, $y + 316, $secondary);
                break;

            case 'tools':
                imagefilledrectangle($image, $x + 142, $y + 170, $x + 290, $y + 230, $primary);
                imagefilledrectangle($image, $x + 174, $y + 126, $x + 258, $y + 170, $primary);
                imagefilledellipse($image, $x + 166, $y + 200, 54, 54, $secondary);
                imagefilledellipse($image, $x + 266, $y + 200, 54, 54, $secondary);
                break;

            case 'box':
                imagefilledpolygon($image, [
                    $x + 126, $y + 128,
                    $x + 214, $y + 86,
                    $x + 314, $y + 128,
                    $x + 226, $y + 174,
                ], 4, $primary);
                imagefilledpolygon($image, [
                    $x + 126, $y + 128,
                    $x + 126, $y + 266,
                    $x + 226, $y + 322,
                    $x + 226, $y + 174,
                ], 4, $primary);
                imagefilledpolygon($image, [
                    $x + 314, $y + 128,
                    $x + 314, $y + 266,
                    $x + 226, $y + 322,
                    $x + 226, $y + 174,
                ], 4, $secondary);
                break;

            default:
                imagefilledellipse($image, $x + (int) ($size / 2), $y + (int) ($size / 2), 210, 210, $primary);
                imagefilledrectangle($image, $x + 140, $y + 188, $x + $size - 140, $y + 236, $secondary);
                break;
        }
    }

    private static function wrapTitle(string $title, int $maxChars, int $maxLines): array
    {
        $words = array_values(array_filter(explode(' ', $title)));
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;

            if (strlen($candidate) <= $maxChars) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            $current = $word;

            if (count($lines) === $maxLines - 1) {
                break;
            }
        }

        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = $current;
        }

        if ($lines === []) {
            $lines[] = substr($title, 0, $maxChars);
        }

        if (count($lines) === $maxLines && strlen(implode(' ', $words)) > strlen(implode(' ', $lines))) {
            $lastIndex = count($lines) - 1;
            $lines[$lastIndex] = rtrim(substr($lines[$lastIndex], 0, max(0, $maxChars - 3))) . '...';
        }

        return $lines;
    }

    private static function fillVerticalGradient($image, int $width, int $height, array $from, array $to): void
    {
        for ($y = 0; $y < $height; $y++) {
            $ratio = $height > 1 ? ($y / ($height - 1)) : 0;
            $color = imagecolorallocate(
                $image,
                (int) round($from[0] + (($to[0] - $from[0]) * $ratio)),
                (int) round($from[1] + (($to[1] - $from[1]) * $ratio)),
                (int) round($from[2] + (($to[2] - $from[2]) * $ratio))
            );
            imageline($image, 0, $y, $width, $y, $color);
        }
    }

    private static function centerText(int $imageWidth, string $text, int $font): int
    {
        return max(16, (int) floor(($imageWidth - (imagefontwidth($font) * strlen($text))) / 2));
    }

    private static function allocate($image, string $hex): int
    {
        [$r, $g, $b] = self::rgb($hex);

        return imagecolorallocate($image, $r, $g, $b);
    }

    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $value = hexdec($hex);

        return [
            ($value >> 16) & 0xFF,
            ($value >> 8) & 0xFF,
            $value & 0xFF,
        ];
    }

    private static function mixHex(string $fromHex, string $toHex, float $ratio): string
    {
        [$fromR, $fromG, $fromB] = self::rgb($fromHex);
        [$toR, $toG, $toB] = self::rgb($toHex);

        $mix = static fn (int $from, int $to): int => (int) round($from + (($to - $from) * $ratio));

        return sprintf('#%02x%02x%02x', $mix($fromR, $toR), $mix($fromG, $toG), $mix($fromB, $toB));
    }
}
