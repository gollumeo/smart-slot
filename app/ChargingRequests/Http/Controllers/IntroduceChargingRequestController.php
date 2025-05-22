<?php

declare(strict_types=1);

namespace App\ChargingRequests\Http\Controllers;

use Illuminate\Http\Request;

final class IntroduceChargingRequestController
{
    public function __construct(private IntroduceChargingRequest $introduce) {}

    public function __invoke(Request $request)
    {

        $chargingRequest = ($this->introduce)(
            $request->user(),
            ChargingRequestPayload::from($request)
        );

        return NarrateChargingRequestIntroduction::from($chargingRequest);
    }
}
