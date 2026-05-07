<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\RoomType;

class PricingService
{
    /**
     * Returns the adjusted price per night for the given room type and date range.
     * Finds active rules that overlap the dates, for this room_type OR null (all types).
     * Highest priority wins; fixed → replaces base_price; percent → base_price × (1 + val/100).
     */
    public function adjustedPrice(RoomType $type, string $checkIn, string $checkOut): float
    {
        $rule = $this->findBestRule($type, $checkIn, $checkOut);

        if (! $rule) {
            return (float) $type->base_price;
        }

        if ($rule->modifier_type === 'fixed') {
            return (float) $rule->modifier_value;
        }

        // percent
        return (float) $type->base_price * (1 + (float) $rule->modifier_value / 100);
    }

    /**
     * Returns the rule name for display as a banner, or null if no rule applies.
     */
    public function activeBanner(RoomType $type, string $checkIn, string $checkOut): ?string
    {
        $rule = $this->findBestRule($type, $checkIn, $checkOut);

        return $rule?->name;
    }

    private function findBestRule(RoomType $type, string $checkIn, string $checkOut): ?PricingRule
    {
        return PricingRule::where('is_active', true)
            ->where('date_from', '<=', $checkOut)
            ->where('date_to', '>=', $checkIn)
            ->where(function ($q) use ($type) {
                $q->whereNull('room_type_id')
                  ->orWhere('room_type_id', $type->id);
            })
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->first();
    }
}
