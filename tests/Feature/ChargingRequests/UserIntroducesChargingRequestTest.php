<?php

declare(strict_types=1);

describe('Feature: User introduces a charging request', function (): void {
    it('joints the queue when no slot is currently free', function (): void {
        // TODO
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
