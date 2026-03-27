<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsReceptionist(): static
    {
        $user = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        return $this->actingAs($user);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'status'      => BookingStatus::Confirmed->value,
            'total_price' => 100000,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // 1. Can add payment to a booking
    // -------------------------------------------------------------------------

    public function test_can_add_payment_to_booking(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAsReceptionist()->post(
            route('payments.store', $booking),
            [
                'amount'  => '50000',
                'method'  => 'cash',
                'paid_at' => now()->format('Y-m-d'),
                'notes'   => 'Test note',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'method'     => 'cash',
        ]);
    }

    // -------------------------------------------------------------------------
    // 2. Payment updates booking payment status
    // -------------------------------------------------------------------------

    public function test_payment_updates_booking_payment_status(): void
    {
        $booking = $this->makeBooking(['total_price' => 100000]);

        $this->actingAsReceptionist()->post(
            route('payments.store', $booking),
            [
                'amount'  => '100000',
                'method'  => 'card',
                'paid_at' => now()->format('Y-m-d'),
                'notes'   => null,
            ]
        );

        $booking->refresh();
        $booking->load('payments');

        $this->assertEquals('paid', $booking->paymentStatus());
    }

    // -------------------------------------------------------------------------
    // 3. Cannot add payment to cancelled booking
    // -------------------------------------------------------------------------

    public function test_cannot_add_payment_to_cancelled_booking(): void
    {
        $booking = $this->makeBooking(['status' => BookingStatus::Cancelled->value]);

        $response = $this->actingAsReceptionist()->post(
            route('payments.store', $booking),
            [
                'amount'  => '50000',
                'method'  => 'cash',
                'paid_at' => now()->format('Y-m-d'),
                'notes'   => null,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('payments', ['booking_id' => $booking->id]);
    }

    // -------------------------------------------------------------------------
    // 4. Payment validates required fields
    // -------------------------------------------------------------------------

    public function test_payment_validates_required_fields(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAsReceptionist()->post(
            route('payments.store', $booking),
            []
        );

        $response->assertSessionHasErrors(['amount', 'method', 'paid_at']);
    }

    // -------------------------------------------------------------------------
    // 5. Payment method must be valid
    // -------------------------------------------------------------------------

    public function test_payment_method_must_be_valid(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAsReceptionist()->post(
            route('payments.store', $booking),
            [
                'amount'  => '50000',
                'method'  => 'bitcoin',
                'paid_at' => now()->format('Y-m-d'),
            ]
        );

        $response->assertSessionHasErrors(['method']);
    }

    // -------------------------------------------------------------------------
    // 6. Unauthenticated user redirected to login
    // -------------------------------------------------------------------------

    public function test_unauthenticated_redirected(): void
    {
        $booking = $this->makeBooking();

        $response = $this->post(
            route('payments.store', $booking),
            [
                'amount'  => '50000',
                'method'  => 'cash',
                'paid_at' => now()->format('Y-m-d'),
            ]
        );

        $response->assertRedirect(route('login'));
    }
}
