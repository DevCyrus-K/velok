<?php

namespace App\Support;

use App\Models\ActivityNotification;
use App\Models\Message;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TopbarData
{
    private const DEFAULT_AVATAR = '/images/users/avatar-1.jpg';

    private const USER_CACHE_PREFIX = 'topbar.user.v2.';

    private const NOTIFICATIONS_CACHE_KEY = 'topbar.notifications';

    public function forUser(?Authenticatable $user): array
    {
        return [
            'user' => $user ? $this->user($user) : null,
            'notifications' => $this->notifications($user),
        ];
    }

    public function user(Authenticatable $user): array
    {
        $cacheKey = self::USER_CACHE_PREFIX.$user->getAuthIdentifier();

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user): array {
            return [
                'id' => $user->getAuthIdentifier(),
                'name' => (string) data_get($user, 'name', 'User'),
                'email' => (string) data_get($user, 'email', ''),
                'avatar' => $this->avatarUrl($user),
                'initials' => $this->initials($user),
                'has_avatar' => $this->hasAvatar($user),
            ];
        });
    }

    public function notifications(?Authenticatable $user = null): array
    {
        return Cache::remember($this->notificationsCacheKey($user), now()->addSeconds(30), function () use ($user): array {
            if ($this->hasActivityNotifications()) {
                return $this->activityNotifications($user);
            }

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

    private function activityNotifications(?Authenticatable $user = null): array
    {
        $query = ActivityNotification::query()
            ->whereNull('read_at');

        $this->scopeNotificationsToUser($query, $user);

        $count = (clone $query)->count();

        $items = $query
            ->latest('occurred_at')
            ->latest('id')
            ->take(8)
            ->get()
            ->map(fn (ActivityNotification $notification): array => [
                'id' => $notification->id,
                'name' => $notification->title,
                'subject' => $notification->body ?: $notification->title,
                'created_at' => $notification->occurred_at?->toIso8601String(),
                'created_at_local' => $notification->occurred_at?->timezone(config('app.timezone'))->format('d M Y, h:i A'),
                'created_at_human' => $notification->occurred_at?->diffForHumans() ?? '',
                'url' => $notification->url ?: '#',
                'mark_read_url' => route('topbar.notifications.read', $notification),
                'icon' => $notification->icon,
                'severity' => $notification->severity,
                'is_read' => (bool) $notification->read_at,
            ])
            ->all();

        return [
            'count' => $count,
            'display_count' => $this->displayCount($count),
            'has_unread' => $count > 0,
            'items' => $items,
            'mark_all_url' => route('topbar.notifications.read-all'),
            'timezone' => config('app.timezone'),
        ];
    }

    private function scopeNotificationsToUser($query, ?Authenticatable $user): void
    {
        $userId = $user?->getAuthIdentifier();

        if ($userId === null) {
            $query->whereNull('user_id');

            return;
        }

        $query->where(function ($query) use ($userId): void {
            $query->whereNull('user_id')
                ->orWhere('user_id', $userId);
        });
    }

    private function hasActivityNotifications(): bool
    {
        try {
            return Schema::hasTable('activity_notifications');
        } catch (\Throwable) {
            return false;
        }
    }

    public function avatarUrl(Authenticatable $user): string
    {
        $avatar = null;

        if ($user instanceof Model) {
            $avatar = $user->getAttribute('avatar')
                ?? $user->getAttribute('avatar_path')
                ?? $user->getAttribute('image_url')
                ?? $user->getAttribute('profile_photo_path');
        }

        if (! is_string($avatar) || trim($avatar) === '') {
            return self::DEFAULT_AVATAR;
        }

        $avatar = trim($avatar);

        if (Str::startsWith($avatar, ['http://', 'https://', '/'])) {
            return $avatar;
        }

        return app(StorageService::class)->url($avatar) ?: self::DEFAULT_AVATAR;
    }

    public function initials(Authenticatable $user): string
    {
        $firstName = trim((string) data_get($user, 'first_name', ''));
        $lastName = trim((string) data_get($user, 'last_name', ''));

        if ($firstName !== '' || $lastName !== '') {
            $initials = collect([$firstName, $lastName])
                ->filter()
                ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
                ->implode('');

            return $initials !== '' ? $initials : 'U';
        }

        $parts = Str::of((string) data_get($user, 'name', ''))
            ->squish()
            ->explode(' ')
            ->filter()
            ->values();

        if ($parts->isEmpty()) {
            return 'U';
        }

        return collect([$parts->first(), $parts->count() > 1 ? $parts->last() : null])
            ->filter()
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
            ?? $user->getAttribute('image_url')
            ?? $user->getAttribute('profile_photo_path');

        return is_string($avatar) && trim($avatar) !== '';
    }

    public function forgetNotifications(): void
    {
        foreach (Cache::get(self::NOTIFICATIONS_CACHE_KEY.'.keys', []) as $cacheKey) {
            Cache::forget($cacheKey);
        }

        Cache::forget(self::NOTIFICATIONS_CACHE_KEY);
        Cache::forget(self::NOTIFICATIONS_CACHE_KEY.'.keys');
    }

    public function forgetUser(User $user): void
    {
        Cache::forget(self::USER_CACHE_PREFIX.$user->getKey());
    }

    public function displayCount(int $count): string
    {
        if ($count > 9) {
            return '9+';
        }

        return (string) max($count, 0);
    }

    private function notificationsCacheKey(?Authenticatable $user = null): string
    {
        $cacheKey = self::NOTIFICATIONS_CACHE_KEY.'.'.($user?->getAuthIdentifier() ?: 'guest');
        $keys = Cache::get(self::NOTIFICATIONS_CACHE_KEY.'.keys', []);

        if (! in_array($cacheKey, $keys, true)) {
            $keys[] = $cacheKey;
            Cache::put(self::NOTIFICATIONS_CACHE_KEY.'.keys', $keys, now()->addDay());
        }

        return $cacheKey;
    }
}
