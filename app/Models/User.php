<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $password
 * @property string $role
 * @property string|null $regency
 * @property bool $is_active
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $role_label
 * @property-read VolunteerProfile|null $volunteerProfile
 * @property-read Collection<int, Shelter> $managedShelters
 *
 * @method static Builder|User query()
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'regency',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKoordinator(): bool
    {
        return $this->role === 'koordinator';
    }

    public function isNakes(): bool
    {
        return $this->role === 'nakes';
    }

    public function isRelawan(): bool
    {
        return $this->role === 'relawan';
    }

    public function isDonatur(): bool
    {
        return $this->role === 'donatur';
    }

    public function isWarga(): bool
    {
        return $this->role === 'warga';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin Pusdalops',
            'koordinator' => 'Koordinator Posko',
            'nakes' => 'Tenaga Kesehatan',
            'relawan' => 'Relawan',
            'donatur' => 'Donatur',
            'warga' => 'Warga Terdampak',
            default => ucfirst($this->role),
        };
    }

    public function volunteerProfile(): HasOne
    {
        return $this->hasOne(VolunteerProfile::class);
    }

    public function managedShelters(): HasMany
    {
        return $this->hasMany(Shelter::class, 'coordinator_id');
    }

    public function canManageShelter(Shelter|int $shelter): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isKoordinator()) {
            return false;
        }

        if ($shelter instanceof Shelter) {
            return (int) $shelter->coordinator_id === (int) $this->id;
        }

        return $this->managedShelters()->where('id', $shelter)->exists();
    }

    public function canAccessModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return match ($module) {
            'modul_a' => in_array($this->role, ['relawan', 'warga']),
            'modul_b' => in_array($this->role, ['koordinator', 'nakes', 'relawan', 'warga']),
            'modul_c' => in_array($this->role, ['koordinator', 'nakes']),
            'modul_d' => in_array($this->role, ['koordinator', 'nakes', 'donatur']),
            'modul_e' => in_array($this->role, ['donatur']),
            'modul_f' => in_array($this->role, ['koordinator']),
            'modul_g' => in_array($this->role, ['koordinator', 'relawan']),
            'users'   => false,
            default   => false,
        };
    }
}
