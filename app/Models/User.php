<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Helpers\NameUser;
use App\Helpers\PhoneHelper;
use App\Helpers\PostalCodeHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public const GENDER_MALE = 1;

    public const GENDER_FEMALE = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'is_pastor',
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
        'must_change_password',
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
            'must_change_password' => 'boolean',
            'birthdate' => 'date',
            'gender' => 'integer',
            'is_pastor' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return NameUser::initials($this->name);
    }

    /**
     * Aplica mascara no campo Phone.
     */
    public function getPhoneAttribute(mixed $value): ?string
    {
        $normalizedValue = $this->stringValue($value);

        return PhoneHelper::format_phone($normalizedValue);
    }

    /**
     * Aplica mascara no campo Postal Code.
     */
    public function getPostalCodeAttribute(mixed $value): ?string
    {
        $normalizedValue = $this->stringValue($value);

        if ($normalizedValue !== null && strlen($normalizedValue) < 8) {
            $normalizedValue = str_pad($normalizedValue, 8, '0', STR_PAD_LEFT);
        }

        return PostalCodeHelper::format_postalcode($normalizedValue);
    }

    /**
     * Remove qualquer caractere não numérico.
     */
    protected function digitsOnly(mixed $value): ?string
    {
        $stringValue = $this->stringValue($value);

        if ($stringValue === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $stringValue);

        return ($digits === '' || $digits === null) ? null : $digits;
    }

    public static function normalizePastorValue(mixed $value): ?int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_int($value)) {
            return $value > 0 ? 1 : 0;
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return null;
        }

        if (is_numeric($stringValue)) {
            return ((int) $stringValue) > 0 ? 1 : 0;
        }

        $normalized = strtoupper($stringValue);

        if (in_array($normalized, ['Y', 'S', 'SIM', 'YES', 'TRUE'], true)) {
            return 1;
        }

        if (in_array($normalized, ['N', 'NAO', 'NÃO', 'NO', 'FALSE'], true)) {
            return 0;
        }

        return null;
    }

    public static function normalizeGenderValue(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            if ($value === self::GENDER_MALE || $value === self::GENDER_FEMALE) {
                return $value;
            }

            return null;
        }

        $stringValue = mb_strtolower(trim((string) $value), 'UTF-8');

        if ($stringValue === '') {
            return null;
        }

        if (in_array($stringValue, ['1', 'm', 'masculino', 'male', 'man', 'homem'], true)) {
            return self::GENDER_MALE;
        }

        if (in_array($stringValue, ['2', 'f', 'feminino', 'female', 'woman', 'mulher'], true)) {
            return self::GENDER_FEMALE;
        }

        return null;
    }

    public static function genderCodeFromValue(mixed $value): ?string
    {
        return match (self::normalizeGenderValue($value)) {
            self::GENDER_MALE => 'M',
            self::GENDER_FEMALE => 'F',
            default => null,
        };
    }

    public static function genderLabelFromValue(mixed $value): ?string
    {
        return match (self::normalizeGenderValue($value)) {
            self::GENDER_MALE => 'Masculino',
            self::GENDER_FEMALE => 'Feminino',
            default => null,
        };
    }

    public function getGenderLabelAttribute(): ?string
    {
        return self::genderLabelFromValue($this->attributes['gender'] ?? null);
    }

    public function getGenderCodeAttribute(): ?string
    {
        return self::genderCodeFromValue($this->attributes['gender'] ?? null);
    }

    public function getPastorAttribute(): ?string
    {
        $normalized = self::normalizePastorValue($this->attributes['is_pastor'] ?? null);

        if ($normalized === null) {
            return null;
        }

        return $normalized === 1 ? 'Y' : 'N';
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

    public function setIsPastorAttribute(mixed $value): void
    {
        $this->attributes['is_pastor'] = self::normalizePastorValue($value);
    }

    public function setPastorAttribute(mixed $value): void
    {
        $this->setIsPastorAttribute($value);
    }

    public function setGenderAttribute(mixed $value): void
    {
        $this->attributes['gender'] = self::normalizeGenderValue($value);
    }

    protected function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : $stringValue;
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

    public function mentoredTrainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'mentors')
            ->withPivot('created_by')
            ->withTimestamps();
    }
}
