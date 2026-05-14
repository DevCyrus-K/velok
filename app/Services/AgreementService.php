<?php

namespace App\Services;

use App\Models\ServiceAgreement;

class AgreementService
{
    public function record(array $data): ServiceAgreement
    {
        // Production cleanup: agreement persistence is centralized for future PDF workflows.
        return ServiceAgreement::query()->create($data);
    }
}
