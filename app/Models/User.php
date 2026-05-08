<?php

namespace App\Models;

use App\Support\TopbarData;
use Database\Factories\UserFactory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    protected static function booted(): void
    {
        $flushUserCache = fn (User $user) => app(TopbarData::class)->forgetUser($user);

        static::saved($flushUserCache);
        static::deleted($flushUserCache);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'job_title',
        'company',
        'location',
        'avatar_path',
        'signature',
        'signature_path',
        'bio',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function signaturePath(): ?string
    {
        $path = $this->getAttribute('signature') ?: $this->getAttribute('signature_path');

        return is_string($path) && trim($path) !== '' ? $path : null;
    }
}
