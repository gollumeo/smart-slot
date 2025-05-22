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

describe('Feature: User ends a charging request', function (): void {
    it('marks the charging request as done and frees the slot', function (): void {
        /** @var TestCase $this */
        $slot = ChargingSlot::factory()->create();
        $user = User::factory()->create();

        $window = new ChargingWindow(
            startTime: CarbonImmutable::now()->subHour(),
            endTime: CarbonImmutable::now()->addHour()
        );

        $request = ChargingRequest::factory()
            ->withWindow($window)
            ->create([
                'user_id' => $user->id,
                'slot_id' => $slot->id,
                'status' => ChargingRequestStatus::CHARGING,
            ]);

        $response = $this->actingAs($user)
            ->postJson("api/charging-requests/{$request->id}/end");

        expect($response->status())->toBe(Response::HTTP_OK);
        $request->refresh();
        expect($request->status)->toBe(ChargingRequestStatus::DONE);
    });

    it('assigns the slot to the next queued request if available', function (): void {
        /** @var TestCase $this */
        $slot = ChargingSlot::factory()->create();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $window = new ChargingWindow(
            CarbonImmutable::now()->subHour(),
            CarbonImmutable::now()->addHour()
        );

        $requestA = ChargingRequest::factory()
            ->withWindow($window)
            ->create([
                'user_id' => $userA->id,
                'slot_id' => $slot->id,
                'status' => ChargingRequestStatus::CHARGING,
            ]);

        $requestB = ChargingRequest::factory()
            ->withWindow($window)
            ->create([
                'user_id' => $userB->id,
                'status' => ChargingRequestStatus::QUEUED,
            ]);

        $response = $this->actingAs($userA)->postJson("/api/charging-requests/{$requestA->id}/end");

        expect($response->status())->toBe(Response::HTTP_OK);

        $requestB->refresh();
        expect($requestB->status)->toBe(ChargingRequestStatus::ASSIGNED)
            ->and($requestB->slot_id)->toBe($slot->id);
    });

    it('rejects the operation if the charging request is already finished', function (): void {
        /** @var TestCase $this */
        $user = User::factory()->create();
        $slot = ChargingSlot::factory()->create();

        $request = ChargingRequest::factory()
            ->create([
                'user_id' => $user->id,
                'slot_id' => $slot->id,
                'status' => ChargingRequestStatus::DONE,
            ]);

        $response = $this->actingAs($user)->postJson("/api/charging-requests/{$request->id}/end");

        expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->and($response->json('message'))->toContain('already finished');
    });
});
