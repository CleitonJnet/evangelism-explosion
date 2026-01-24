<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Helpers\PhoneHelper;
use App\Helpers\PostalCodeHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pastor',
        'name',
        'birthdate',
        'gender',
        'phone',
        'email',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'postal_code',
        'password',
        'church_id',
        'church_temp_id',
        'notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Aplica mascara no campo Phone.
     */
    public function getPhoneAttribute(?string $value): ?string
    {
        return PhoneHelper::format_phone($value);
    }

    /**
     * Aplica mascara no campo Postal Code.
     */
    public function getPostalCodeAttribute(?string $value): ?string
    {
        return PostalCodeHelper::format_postalcode($value);
    }

    /**
     * Remove qualquer caractere não numérico.
     */
    protected function digitsOnly(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return ($digits === '' || $digits === null) ? null : $digits;
    }

    /**
     * Salva telefone sem máscara.
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $this->digitsOnly($value);
    }

    /**
     * Salva celular sem máscara.
     */
    public function setMobileAttribute($value): void
    {
        $this->attributes['mobile'] = $this->digitsOnly($value);
    }

    /**
     * Salva WhatsApp sem máscara.
     */
    public function setWhatsappAttribute($value): void
    {
        $this->attributes['whatsapp'] = $this->digitsOnly($value);
    }

    /**
     * Salva CEP sem máscara.
     */
    public function setPostalCodeAttribute($value): void
    {
        $this->attributes['postal_code'] = $this->digitsOnly($value);
    }

    public function hasRole(string $roleName): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('name', $roleName);
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function church_temp(): BelongsTo
    {
        return $this->belongsTo(ChurchTemp::class);
    }

    public function courseAsTeacher(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->withPivot('status');
    }

    public function trainingsAsTeacher(): HasMany
    {
        return $this->hasMany(Training::class, 'teacher_id', 'id');
    }

    public function trainingsAsStudent(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'training_user')
            ->withPivot(['accredited', 'kit', 'payment', 'payment_receipt']);
    }

    public function churches(): BelongsToMany
    {
        return $this->belongsToMany(Church::class, 'church_missionary', 'church_id', 'user_id');
    }

    public function hostChurches(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\HostChurch::class, 'host_church_admins')
            ->withPivot(['certified_at', 'status'])
            ->withTimestamps();
    }
}
