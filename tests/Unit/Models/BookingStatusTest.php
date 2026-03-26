<?php

namespace Tests\Unit\Models;

use App\Enums\BookingStatus;
use PHPUnit\Framework\TestCase;

class BookingStatusTest extends TestCase
{
    public function test_pending_can_transition_to_confirmed(): void
    {
        $this->assertTrue(BookingStatus::Pending->canTransitionTo(BookingStatus::Confirmed));
    }

    public function test_pending_can_transition_to_checked_in(): void
    {
        $this->assertTrue(BookingStatus::Pending->canTransitionTo(BookingStatus::CheckedIn));
    }

    public function test_pending_can_transition_to_cancelled(): void
    {
        $this->assertTrue(BookingStatus::Pending->canTransitionTo(BookingStatus::Cancelled));
    }

    public function test_confirmed_can_transition_to_checked_in(): void
    {
        $this->assertTrue(BookingStatus::Confirmed->canTransitionTo(BookingStatus::CheckedIn));
    }

    public function test_confirmed_can_transition_to_cancelled(): void
    {
        $this->assertTrue(BookingStatus::Confirmed->canTransitionTo(BookingStatus::Cancelled));
    }

    public function test_checked_in_can_transition_to_checked_out(): void
    {
        $this->assertTrue(BookingStatus::CheckedIn->canTransitionTo(BookingStatus::CheckedOut));
    }

    public function test_checked_in_can_transition_to_cancelled(): void
    {
        $this->assertTrue(BookingStatus::CheckedIn->canTransitionTo(BookingStatus::Cancelled));
    }

    public function test_checked_out_cannot_transition_to_confirmed(): void
    {
        $this->assertFalse(BookingStatus::CheckedOut->canTransitionTo(BookingStatus::Confirmed));
    }

    public function test_checked_out_has_no_allowed_transitions(): void
    {
        $this->assertEmpty(BookingStatus::CheckedOut->allowedTransitions());
    }

    public function test_cancelled_has_no_allowed_transitions(): void
    {
        $this->assertEmpty(BookingStatus::Cancelled->allowedTransitions());
    }

    public function test_cancelled_cannot_transition_to_pending(): void
    {
        $this->assertFalse(BookingStatus::Cancelled->canTransitionTo(BookingStatus::Pending));
    }

    public function test_pending_cannot_transition_to_checked_out(): void
    {
        $this->assertFalse(BookingStatus::Pending->canTransitionTo(BookingStatus::CheckedOut));
    }

    public function test_confirmed_cannot_transition_to_pending(): void
    {
        $this->assertFalse(BookingStatus::Confirmed->canTransitionTo(BookingStatus::Pending));
    }

    public function test_checked_in_cannot_transition_to_confirmed(): void
    {
        $this->assertFalse(BookingStatus::CheckedIn->canTransitionTo(BookingStatus::Confirmed));
    }
}
