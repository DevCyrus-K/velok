<?php

use App\Models\CareerJob;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function careerJobPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Property Sales Associate',
        'department' => 'Sales',
        'location' => 'Nairobi',
        'employment_type' => 'Full-time',
        'salary_range' => 'Competitive',
        'summary' => 'Help qualified property seekers contact sellers faster.',
        'description' => 'Follow up with leads and support property viewings.',
        'requirements' => 'Strong communication and real estate experience.',
        'status' => CareerJob::STATUS_OPEN,
        'posted_at' => '2026-05-07T09:00',
        'closes_at' => null,
    ], $overrides);
}

it('lists career jobs in the admin UI', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('careers.jobs.store'), careerJobPayload());

    $job = CareerJob::query()->first();

    $response->assertRedirect(route('careers.jobs.show', $job));

    $this->assertDatabaseHas('career_jobs', [
        'title' => 'Property Sales Associate',
        'status' => CareerJob::STATUS_OPEN,
    ]);

    $this->actingAs($user)
        ->get(route('careers.jobs.index'))
        ->assertOk()
        ->assertSee('Property Sales Associate')
        ->assertSee('Open Jobs');
});

it('accepts a public career application and lets admin review it', function () {
    $user = User::factory()->create();
    $job = CareerJob::query()->create(careerJobPayload([
        'title' => 'Viewing Coordinator',
    ]));

    $this->getJson('/api/careers/jobs')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Viewing Coordinator');

    $this->postJson('/api/careers/applications', [
        'job_id' => $job->id,
        'applicant_name' => 'Grace Applicant',
        'email' => 'grace@example.com',
        'phone' => '+254700222333',
        'current_location' => 'Westlands',
        'cover_letter' => 'I can help applicants and sellers connect quickly.',
        'source_page' => '/careers',
    ])->assertCreated()
        ->assertJsonPath('success', true);

    $application = JobApplication::query()->first();

    $this->assertDatabaseHas('job_applications', [
        'career_job_id' => $job->id,
        'job_title' => 'Viewing Coordinator',
        'applicant_name' => 'Grace Applicant',
        'status' => JobApplication::STATUS_NEW,
    ]);

    $this->actingAs($user)
        ->get(route('careers.applications.index'))
        ->assertOk()
        ->assertSee('Grace Applicant')
        ->assertSee('Viewing Coordinator');

    $this->actingAs($user)
        ->patch(route('careers.applications.status', $application), [
            'status' => JobApplication::STATUS_SHORTLISTED,
            'notes' => 'Strong fit for immediate follow up.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('job_applications', [
        'id' => $application->id,
        'status' => JobApplication::STATUS_SHORTLISTED,
        'notes' => 'Strong fit for immediate follow up.',
    ]);
});

it('rejects applications for closed jobs', function () {
    $job = CareerJob::query()->create(careerJobPayload([
        'status' => CareerJob::STATUS_CLOSED,
    ]));

    $this->postJson('/api/careers/applications', [
        'job_id' => $job->id,
        'applicant_name' => 'Late Applicant',
        'email' => 'late@example.com',
        'phone' => '+254700222444',
    ])->assertStatus(422)
        ->assertJsonPath('success', false);
});
