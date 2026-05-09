<?php

namespace App\Support;

use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TopbarData
{
    private const DEFAULT_AVATAR = '/images/users/avatar-1.jpg';
    private const USER_CACHE_PREFIX = 'topbar.user.';
    private const NOTIFICATIONS_CACHE_KEY = 'topbar.notifications';

    public function forUser(?Authenticatable $user): array
    {
        return [
            'user' => $user ? $this->user($user) : null,
            'notifications' => $this->notifications(),
        ];
    }

    public function user(Authenticatable $user): array
    {
        $cacheKey = self::USER_CACHE_PREFIX . $user->getAuthIdentifier();

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user): array {
            return [
                'id' => $user->getAuthIdentifier(),
                'name' => (string) data_get($user, 'name', 'User'),
                'email' => (string) data_get($user, 'email', ''),
                'avatar' => $this->avatarUrl($user),
            ];
        });
    }

    public function notifications(): array
    {
        return Cache::remember(self::NOTIFICATIONS_CACHE_KEY, now()->addSeconds(30), function (): array {
            $count = Message::query()
                ->where('status', 'unread')
                ->count();

            $items = Message::query()
                ->where('status', 'unread')
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'subject', 'created_at'])
                ->map(function (Message $message): array {
                    return [
                        'id' => $message->id,
                        'name' => $message->name,
                        'subject' => $message->subject,
                        'created_at' => $message->created_at?->toIso8601String(),
                        'created_at_human' => $message->created_at?->diffForHumans() ?? '',
                        'url' => route('messages.show', $message),
                    ];
                })
                ->all();

            return [
                'count' => $count,
                'display_count' => $this->displayCount($count),
                'has_unread' => $count > 0,
                'items' => $items,
            ];
        });
    }

    public function avatarUrl(Authenticatable $user): string
    {
        $avatar = null;

        if ($user instanceof Model) {
            $avatar = $user->getAttribute('avatar')
                ?? $user->getAttribute('avatar_path')
                ?? $user->getAttribute('profile_photo_path');
        }

        if (! is_string($avatar) || $avatar === '') {
            return self::DEFAULT_AVATAR;
        }

        if (Str::startsWith($avatar, ['http://', 'https://', '/'])) {
            return $avatar;
        }

        return Storage::url($avatar);
    }

    public function initials(Authenticatable $user): string
    {
        $name = trim((string) data_get($user, 'name', ''));

        if ($name === '') {
            return 'U';
        }

        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    public function hasAvatar(Authenticatable $user): bool
    {
        if (! $user instanceof Model) {
            return false;
        }

        $avatar = $user->getAttribute('avatar')
            ?? $user->getAttribute('avatar_path')
            ?? $user->getAttribute('profile_photo_path');

        return is_string($avatar) && trim($avatar) !== '';
    }

    public function forgetNotifications(): void
    {
        Cache::forget(self::NOTIFICATIONS_CACHE_KEY);
    }

    public function forgetUser(User $user): void
    {
        Cache::forget(self::USER_CACHE_PREFIX . $user->getKey());
    }

    public function displayCount(int $count): string
    {
        if ($count > 9) {
            return '9+';
        }

        return (string) max($count, 0);
    }
}
