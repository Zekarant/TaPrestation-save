<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\FoodProduct;
use App\Models\Prestataire;
use App\Models\Service;
use App\Models\UrgentSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeedFrenchMarketplaceDemoCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_marketplace_command_creates_all_listing_types(): void
    {
        Storage::fake('public');

        $parent = Category::create([
            'name' => 'Demo parent',
            'description' => 'Demo parent',
            'parent_id' => null,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Demo child',
            'description' => 'Demo child',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        Http::fake([
            'https://api.openverse.org/*' => Http::response([
                'results' => [
                    [
                        'thumbnail' => 'https://cdn.test/demo-image.jpg',
                        'url' => 'https://cdn.test/demo-image.jpg',
                        'mature' => false,
                    ],
                ],
            ], 200),
            'https://cdn.test/*' => Http::response('fake-image-content', 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $this->artisan('demo:seed-marketplace', [
            '--count' => 8,
            '--refresh' => true,
        ])->assertExitCode(0);

        $this->assertSame(8, User::where('email', 'like', '%@demo-fr.example')->count());
        $this->assertSame(8, Prestataire::count());
        $this->assertGreaterThan(0, Service::count());
        $this->assertGreaterThan(0, Equipment::count());
        $this->assertGreaterThan(0, UrgentSale::count());
        $this->assertGreaterThan(0, FoodProduct::count());

        $this->assertNotEmpty(Storage::disk('public')->allFiles('demo-marketplace/avatars'));
        $this->assertNotEmpty(Storage::disk('public')->allFiles('demo-marketplace/cache'));
    }
}
