<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function fakeSignaturePng(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');
}

function fakeSignatureUpload(): UploadedFile
{
    return UploadedFile::fake()->createWithContent(
        'signature.png',
        fakeSignaturePng()
    );
}

it('shows the authenticated user account page', function () {
    $user = User::factory()->create([
        'name' => 'Velok Admin',
        'email' => 'admin@example.com',
        'phone' => '+254700000000',
        'job_title' => 'Sales Manager',
    ]);

    $this->actingAs($user)
        ->get(route('account.show'))
        ->assertOk()
        ->assertSee('My Account')
        ->assertSee('Velok Admin')
        ->assertSee('admin@example.com')
        ->assertSee('Security');
});

it('updates account profile information', function () {
    Storage::fake('local');

    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($user)
        ->patch(route('account.profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '+254711111111',
            'job_title' => 'Property Advisor',
            'signature_upload' => fakeSignatureUpload(),
        ])
        ->assertRedirect(route('account.show'))
        ->assertSessionHas('user_name', 'New Name')
        ->assertSessionHas('user_email', 'new@example.com');

    $user->refresh();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
        'phone' => '+254711111111',
        'job_title' => 'Property Advisor',
    ]);

    $this->assertStringStartsWith('general/signature_', $user->signature_path);
    expect($user->signature)->toBe($user->signature_path);
});

it('stores a drawn account signature path', function () {
    Storage::fake('local');

    $user = User::factory()->create([
        'name' => 'Signer',
        'email' => 'signer@example.com',
    ]);

    $this->actingAs($user)
        ->patch(route('account.profile.update'), [
            'name' => 'Signer',
            'email' => 'signer@example.com',
            'signature_data' => 'data:image/png;base64,'.base64_encode(fakeSignaturePng()),
        ])
        ->assertRedirect(route('account.show'));

    $user->refresh();

    $this->assertStringStartsWith('general/signature-'.$user->id.'-', $user->signature_path);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'signature' => $user->signature_path,
        'signature_path' => $user->signature_path,
    ]);
});

it('serves the authenticated users signature through the protected account route', function () {
    Storage::fake('local');

    $user = User::factory()->create([
        'signature' => 'signatures/protected.png',
        'signature_path' => 'signatures/protected.png',
    ]);

    Storage::disk('local')->put(
        'signatures/protected.png',
        fakeSignaturePng()
    );

    $this->get(route('account.signature'))->assertRedirect(route('login'));

    $this->actingAs($user)
        ->get(route('account.signature'))
        ->assertOk()
        ->assertHeader('content-type', 'image/png');
});

it('updates account password when the current password matches', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->patch(route('account.security.update'), [
            'current_password' => 'password',
            'password' => 'NewSecurePassword1',
            'password_confirmation' => 'NewSecurePassword1',
        ])
        ->assertRedirect(route('account.show'));

    expect(Hash::check('NewSecurePassword1', $user->refresh()->password))->toBeTrue();
});
