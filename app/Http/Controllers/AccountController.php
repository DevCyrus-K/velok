<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountProfileRequest;
use App\Models\User;
use App\Support\TopbarData;
use App\Support\UserSignature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly TopbarData $topbarData,
        private readonly UserSignature $userSignature,
    ) {}

    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('account.show', [
            'user' => $user,
            'avatarUrl' => $this->topbarData->avatarUrl($user),
            'signatureUrl' => $this->signatureUrl($user),
            'securityContext' => [
                'ip_address' => $request->ip() ?: 'Unknown',
                'user_agent' => Str::limit($request->userAgent() ?: 'Unknown device', 120),
            ],
        ]);
    }

    public function updateProfile(AccountProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->profileData();
        $signatureData = $request->validated('signature_data');
        $emailChanged = $validated['email'] !== $user->email;

        if ($request->hasFile('avatar')) {
            $this->deleteStoredAvatar($user);
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('signature_upload')) {
            $this->deleteStoredSignature($user);
            $this->setSignaturePath($validated, $this->userSignature->storeUploaded($request->file('signature_upload')));
        } elseif (is_string($signatureData) && trim($signatureData) !== '') {
            $this->deleteStoredSignature($user);
            $this->setSignaturePath($validated, $this->userSignature->storeDrawn($signatureData, $user));
        }

        $user->fill($validated);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->refreshUserSession($request, $user);

        return redirect()
            ->route('account.show')
            ->with('account_tab', 'profile')
            ->with($emailChanged ? 'toast-info' : 'toast-success', $emailChanged
                ? 'Account updated. Please verify your new email address.'
                : 'Account information updated.');
    }

    public function signature(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $path = $this->userSignature->path($user);
        $content = $this->userSignature->content($path);

        abort_if($content === null, 404);

        return response($content, 200, [
            'Content-Type' => $this->userSignature->mimeType($path) ?: 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        $request->session()->regenerate();
        $this->refreshUserSession($request, $user);

        return redirect()
            ->route('account.show')
            ->with('account_tab', 'security')
            ->with('toast-success', 'Password updated successfully.');
    }

    private function refreshUserSession(Request $request, User $user): void
    {
        $user->refresh();

        $request->session()->put('user_name', $user->name);
        $request->session()->put('user_email', $user->email);
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_avatar', $this->topbarData->avatarUrl($user));
    }

    private function deleteStoredAvatar(User $user): void
    {
        $avatarPath = $user->avatar_path;

        if (! is_string($avatarPath) || $avatarPath === '' || Str::startsWith($avatarPath, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk('public')->delete($avatarPath);
    }

    private function signatureUrl(User $user): ?string
    {
        return $this->userSignature->routeUrl($user);
    }

    private function deleteStoredSignature(User $user): void
    {
        $this->userSignature->delete($user->getAttribute('signature'));
        $this->userSignature->delete($user->getAttribute('signature_path'));
    }

    private function setSignaturePath(array &$validated, string $path): void
    {
        if (Schema::hasColumn('users', 'signature')) {
            $validated['signature'] = $path;
        }

        if (Schema::hasColumn('users', 'signature_path')) {
            $validated['signature_path'] = $path;
        }
    }
}
