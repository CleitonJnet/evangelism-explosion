<?php

namespace App\Models;

use App\Helpers\PhoneHelper;
use App\Helpers\PostalCodeHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Church extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pastor',
        'email',
        'phone',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'postal_code',
        'contact',
        'contact_phone',
        'contact_email',
        'notes',
        'logo',
        'state',
    ];

    /**
     * Aplica mascara no campo telefone da igreja.
     */
    public function getPhoneAttribute($value)
    {
        return PhoneHelper::format_phone($value);
    }

    /**
     * Aplica mascara no campo telefone do Contato.
     */
    public function getContactPhoneAttribute($value)
    {
        return PhoneHelper::format_phone($value);
    }

    /**
     * Aplica mascara no campo CEP.
     */
    public function getPostalCodeAttribute($value)
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
     * Salva o telefone da igreja sem máscara.
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $this->digitsOnly($value);
    }

    /**
     * Salva o CEP sem máscara.
     */
    public function setPostalCodeAttribute($value): void
    {
        $this->attributes['postal_code'] = $this->digitsOnly($value);
    }

    /**
     * Salva o telefone do contato sem máscara.
     */
    public function setContactPhoneAttribute($value): void
    {
        $this->attributes['contact_phone'] = $this->digitsOnly($value);
    }

    public function members()
    {
        return $this->hasMany(User::class);
    }

    public function missionaries()
    {
        return $this->belongsToMany(User::class, 'church_missionary', 'church_id', 'user_id');
    }

    public function hostChurch()
    {
        return $this->hasOne(\App\Models\HostChurch::class);
    }
}
