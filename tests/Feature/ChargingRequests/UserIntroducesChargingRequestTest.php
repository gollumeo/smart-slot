<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingSlots\ChargingSlot;
use App\Users\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

describe('Feature: User introduces a charging request', function (): void {
    it('joins the queue when no slot is currently free', function (): void {
        /** @var TestCase $this */
        ChargingSlot::factory(2)->create();

        ChargingRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'slot_id' => ChargingSlot::first()?->id,
            'status' => ChargingRequestStatus::CHARGING,
        ]);

        ChargingRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'slot_id' => ChargingSlot::first()?->id,
            'status' => ChargingRequestStatus::ASSIGNED,
        ]);

        $user = $this->createStaticTestUser();

        $payload = [
            'battery_percentage' => 42,
            'charging_window' => [
                'start_time' => now()->addHour()->toString(),
                'end_time' => now()->addHour()->toString(),
            ],
        ];

        $response = $this->actingAs($user)->postJson('/charging-requests', $payload);

        expect($response->status())->toBe(Response::HTTP_CREATED);

        /** @var array{slot_id: int|null, status: string} $data */
        $data = $response->json('data');
        expect($data['slot_id'])->toBeNull()
            ->and($data['status'])->toBe(ChargingRequestStatus::QUEUED);
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
