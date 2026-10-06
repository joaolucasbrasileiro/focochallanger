<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Permission;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function memberships(): HasMany
    {
        return $this->hasMany(HotelMembership::class);
    }

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'hotel_memberships')
            ->withPivot(['id', 'role'])
            ->withTimestamps();
    }

    public function membershipFor(Hotel|int $hotel): ?HotelMembership
    {
        $hotelId = $hotel instanceof Hotel ? $hotel->getKey() : $hotel;

        return $this->memberships()->where('hotel_id', $hotelId)->first();
    }

    public function hasPermissionForHotel(Hotel|int $hotel, Permission $permission): bool
    {
        $hotelId = $hotel instanceof Hotel ? $hotel->getKey() : $hotel;

        return $this->memberships()
            ->where('hotel_id', $hotelId)
            ->whereIn('role', $this->rolesAllowing($permission))
            ->exists();
    }

    public function hasPermissionInAnyHotel(Permission $permission): bool
    {
        return $this->memberships()
            ->whereIn('role', $this->rolesAllowing($permission))
            ->exists();
    }

    /**
     * @return Collection<int, int>
     */
    public function hotelIdsWithPermission(Permission $permission): Collection
    {
        return $this->memberships()
            ->whereIn('role', $this->rolesAllowing($permission))
            ->pluck('hotel_id');
    }

    /**
     * @return list<string>
     */
    private function rolesAllowing(Permission $permission): array
    {
        return array_values(array_map(
            fn (UserRole $role): string => $role->value,
            array_filter(
                UserRole::cases(),
                fn (UserRole $role): bool => $role->allows($permission),
            ),
        ));
    }

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
}
