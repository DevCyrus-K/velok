<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountProfileRequest;
use App\Models\User;
use App\Support\NotificationLogger;
use App\Support\TopbarData;
use App\Support\TwoFactorOtp;
use App\Support\UserSignature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly TopbarData $topbarData,
        private readonly TwoFactorOtp $twoFactorOtp,
        private readonly UserSignature $userSignature,
    ) {}

    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        app(NotificationLogger::class)->markReadFor($user);

        return view('account.show', [
            'user' => $user,
            'avatarUrl' => $this->topbarData->avatarUrl($user),
            'signatureUrl' => $this->signatureUrl($user),
            'securityContext' => [
                'ip_address' => $request->ip() ?: 'Unknown',
                'user_agent' => Str::limit($request->userAgent() ?: 'Unknown device', 120),
            ],
            'activeSessions' => $this->activeSessions($request),
            'canListSessions' => $this->canListSessions(),
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
        app(NotificationLogger::class)->accountActivity(
            $user,
            $emailChanged ? 'Account email changed' : 'Account profile updated',
            $user->email.' updated profile details.',
            'circle-user'
        );

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
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        $request->session()->regenerate();
        $this->refreshUserSession($request, $user);
        app(NotificationLogger::class)->passwordChanged($user, $request);

        return redirect()
            ->route('account.show')
            ->with('account_tab', 'security')
            ->with('toast-success', 'Password updated successfully.');
    }

    public function requestTwoFactorEnable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return redirect()
                ->route('account.show')
                ->with('account_tab', 'security')
                ->with('toast-info', 'Two-factor authentication is already enabled.');
        }

        $this->twoFactorOtp->send($user);
        $request->session()->put('two_factor_setup_pending', true);

        return redirect()
            ->route('account.show')
            ->with('account_tab', 'security')
            ->with('toast-success', 'A test verification code has been sent to your email.');
    }

    public function confirmTwoFactorEnable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->merge([
            'otp' => preg_replace('/\D+/', '', (string) $request->input('otp')),
        ]);

        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if (! $request->session()->get('two_factor_setup_pending')) {
            return redirect()
                ->route('account.show')
                ->with('account_tab', 'security')
                ->with('toast-error', 'Please request a verification code first.');
        }

        $status = $this->twoFactorOtp->verify($user, (string) $request->input('otp'));

        if ($status === TwoFactorOtp::STATUS_VALID) {
            $user->forceFill(['two_factor_enabled' => true])->save();
            $request->session()->forget('two_factor_setup_pending');
            $this->refreshUserSession($request, $user);
            app(NotificationLogger::class)->accountActivity($user, 'Two-factor enabled', $user->email.' enabled two-factor authentication.', 'shield-check');

            return redirect()
                ->route('account.show')
                ->with('account_tab', 'security')
                ->with('toast-success', 'Two-factor authentication is now enabled.');
        }

        if (in_array($status, [TwoFactorOtp::STATUS_EXPIRED, TwoFactorOtp::STATUS_MISSING], true)) {
            $request->session()->forget('two_factor_setup_pending');

            return redirect()
                ->route('account.show')
                ->with('account_tab', 'security')
                ->withErrors(['otp' => 'Code expired. Please request a new verification code.'])
                ->with('toast-error', 'Code expired. Please request a new verification code.');
        }

        if ($status === TwoFactorOtp::STATUS_LOCKED) {
            $request->session()->forget('two_factor_setup_pending');

            return redirect()
                ->route('account.show')
                ->with('account_tab', 'security')
                ->withErrors(['otp' => 'Too many attempts. Please request a new verification code.'])
                ->with('toast-error', 'Too many attempts. Please request a new verification code.');
        }

        return redirect()
            ->route('account.show')
            ->with('account_tab', 'security')
            ->withErrors(['otp' => 'The verification code is invalid.'])
            ->with('toast-error', 'The verification code is invalid.');
    }

    public function disableTwoFactor(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password:web'],
        ]);

        $user->forceFill(['two_factor_enabled' => false])->save();
        $this->twoFactorOtp->clear($user);
        $request->session()->forget('two_factor_setup_pending');
        $this->refreshUserSession($request, $user);
        app(NotificationLogger::class)->accountActivity($user, 'Two-factor disabled', $user->email.' disabled two-factor authentication.', 'shield-x');

        return redirect()
            ->route('account.show')
            ->with('account_tab', 'security')
            ->with('toast-success', 'Two-factor authentication has been disabled.');
    }

    public function logoutOtherDevices(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:web'],
        ]);

        Auth::logoutOtherDevices($validated['current_password']);

        if ($this->canListSessions()) {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $request->session()->regenerate();
        app(NotificationLogger::class)->accountActivity($request->user(), 'Other devices logged out', $request->user()->email.' logged out other sessions.', 'monitor-x');

        return redirect()
            ->route('account.show')
            ->with('account_tab', 'security')
            ->with('toast-success', 'All other devices have been logged out.');
    }

    private function refreshUserSession(Request $request, User $user): void
    {
        $user->refresh();

        $request->session()->put('user_name', $user->name);
        $request->session()->put('user_email', $user->email);
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_avatar', $this->topbarData->avatarUrl($user));
        $request->session()->put('user_avatar_initials', $this->topbarData->initials($user));
        $request->session()->put('user_has_avatar', $this->topbarData->hasAvatar($user));
    }

    private function activeSessions(Request $request): array
    {
        if (! $this->canListSessions()) {
            return [[
                'id' => $request->session()->getId(),
                'ip_address' => $request->ip() ?: 'Unknown',
                'user_agent' => Str::limit($request->userAgent() ?: 'Unknown device', 120),
                'last_active' => now(),
                'is_current' => true,
            ]];
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('last_activity')
            ->limit(12)
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'ip_address' => $session->ip_address ?: 'Unknown',
                'user_agent' => Str::limit($session->user_agent ?: 'Unknown device', 120),
                'last_active' => now()->setTimestamp((int) $session->last_activity),
                'is_current' => hash_equals((string) $session->id, $request->session()->getId()),
            ])
            ->all();
    }

    private function canListSessions(): bool
    {
        try {
            return config('session.driver') === 'database' && Schema::hasTable(config('session.table', 'sessions'));
        } catch (\Throwable) {
            return false;
        }
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
