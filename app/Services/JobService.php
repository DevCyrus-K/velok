<?php

namespace App\Services;

use App\Models\CareerJob;
use App\Models\JobApplication;

class JobService
{
    public function createCareerJob(array $data): CareerJob
    {
        // Production cleanup: career job writes have a service entry point.
        return CareerJob::query()->create($data);
    }

    public function createApplication(array $data): JobApplication
    {
        return JobApplication::query()->create($data);
    }
}
