<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingSlots\ChargingSlot;
use App\Users\User;
use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

describe('Feature: User introduces a charging request', function (): void {
    it('joins the queue when no slot is currently free', function (): void {
        /** @var TestCase $this */
        $start = CarbonImmutable::parse('2025-05-22 09:00');
        $end = CarbonImmutable::parse('2025-05-22 11:00');
        $window = new ChargingWindow($start, $end);

        $slots = ChargingSlot::factory(2)->create()->values();

        $slotA = $slots->get(0);
        $slotB = $slots->get(1);

        assert($slotA !== null && $slotB !== null);

        ChargingRequest::factory()
            ->withWindow($window)
            ->create([
                'user_id' => User::factory()->create()->id,
                'slot_id' => $slotA->id,
                'status' => ChargingRequestStatus::CHARGING,
            ]);

        ChargingRequest::factory()
            ->withWindow($window)
            ->create([
                'user_id' => User::factory()->create()->id,
                'slot_id' => $slotB->id,
                'status' => ChargingRequestStatus::ASSIGNED,
            ]);

        $user = User::factory()->create();

        $payload = [
            'battery_percentage' => 42,
            'charging_window' => [
                'start_time' => '22-05-2025 09:00',
                'end_time' => '22-05-2025 11:00',
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/charging-requests', $payload);

        expect($response->status())->toBe(Response::HTTP_CREATED, $response->content());

        /** @var array{slot_id: int|null, status: string} $data */
        $data = $response->json('data');

        // Debug lisible sans crash
        dump($data);

        expect($data['slot_id'])->toBeNull()
            ->and($data['status'])->toBe(ChargingRequestStatus::QUEUED->value);
    });

    it('is assigned a slot immediately when one is available', function (): void {
        // TODO
    });

    it('is rejected if the user already has an ongoing charging session', function (): void {
        // TODO
    });

    it('receives a clear confirmation containing the request status and ID', function (): void {
        // TODO
    });

    it('cannot inject invalid battery percentages or malformed charging windows', function (): void {
        // TODO
    });

    it('cannot start a request in the past', function (): void {
        // TODO
    });
});
