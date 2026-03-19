<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\PDF;
use Illuminate\Support\Facades\DB;
use App\Mail\InvoiceMail;
use App\Models\User;

class AdminFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_refund_creates_pending_refund()
    {
        // Préparer l'utilisateur et transaction
        $user = User::factory()->create();

        $transactionId = DB::table('transactions')->insertGetId([
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->withoutMiddleware();

        $response = $this->post(route('admin.finance.refunds.store'), [
            'transaction_id' => $transactionId,
            'amount' => 50,
            'reason' => 'Test refund',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('refunds', [
            'transaction_id' => $transactionId,
            'amount' => 50,
            'status' => 'pending',
        ]);
    }

    public function test_send_invoice_stores_pdf_and_sends_email()
    {
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->create(['email' => 'client@example.test']);

        $invoiceId = DB::table('invoices')->insertGetId([
            'user_id' => $user->id,
            'number' => 'INV-1001',
            'total' => 150.00,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('invoice_items')->insert([
            ['invoice_id' => $invoiceId, 'description' => 'Service A', 'price' => 150, 'quantity' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Stub PDF facade to avoid dependency on package during tests
        if (method_exists(PDF::class, 'shouldReceive')) {
            PDF::shouldReceive('loadView')->andReturnSelf();
            PDF::shouldReceive('output')->andReturn('%PDF-DATA%');
        }

        $this->actingAs($user)->withoutMiddleware();

        $response = $this->post(route('admin.finance.invoices.send', $invoiceId));

        $response->assertRedirect();

        // PDF stored
        $this->assertTrue(Storage::disk('local')->exists('invoices/facture-' . 'INV-1001' . '.pdf'));

        // Mail sent
        Mail::assertSent(InvoiceMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}
