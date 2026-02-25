<?php

namespace App\Models;

use App\Helpers\MoneyHelper;
use App\Helpers\PhoneHelper;
use App\Helpers\PostalCodeHelper;
use App\TrainingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Training extends Model
{
    use HasFactory;

    private const DEFAULT_PIX_KEY = 'eebrasil@eebrasil.org.br';

    private const DEFAULT_PIX_QR_CODE_ASSET_PATH = 'images/qrcode-pix-ee.webp';

    protected $fillable = ['course_id', 'teacher_id', 'church_id', 'coordinator', 'leader', 'banner', 'url', 'gpwhatsapp', 'phone', 'email', 'street', 'number', 'complement', 'district', 'city', 'state', 'postal_code', 'price', 'price_church', 'discount', 'pix_qr_code', 'pix_key', 'kits', 'totNewChurches', 'totKitsReceived', 'totApproaches', 'totDecisions', 'totListeners', 'notes', 'status', 'welcome_duration_minutes', 'schedule_settings', 'schedule_attention_shown_at', 'schedule_adjusted_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TrainingStatus::class,
            'welcome_duration_minutes' => 'integer',
            'schedule_settings' => 'array',
            'schedule_attention_shown_at' => 'datetime',
            'schedule_adjusted_at' => 'datetime',
        ];
    }

    public function statusKey(): string
    {
        $status = $this->status;

        if ($status instanceof TrainingStatus) {
            return $status->key();
        }

        $statusEnum = TrainingStatus::tryFrom((int) $status);

        return $statusEnum?->key() ?? TrainingStatus::Planning->key();
    }

    public function getPhoneAttribute(?string $value): ?string
    {
        return PhoneHelper::format_phone($value);
    }

    public function getPostalCodeAttribute(?string $value): ?string
    {
        return PostalCodeHelper::format_postalcode($value);
    }

    public function getPriceAttribute(string|int|float|null $value): ?string
    {
        return MoneyHelper::format_money($value);
    }

    public function getPriceChurchAttribute(string|int|float|null $value): ?string
    {
        return MoneyHelper::format_money($value);
    }

    public function getDiscountAttribute(string|int|float|null $value): ?string
    {
        return MoneyHelper::format_money($value);
    }

    public function getPaymentAttribute(): ?string
    {
        $rawPrice = $this->attributes['price'] ?? $this->getRawOriginal('price');
        $rawPriceChurch = $this->attributes['price_church'] ?? $this->getRawOriginal('price_church');
        $rawDiscount = $this->attributes['discount'] ?? $this->getRawOriginal('discount');

        $price = MoneyHelper::toFloat($rawPrice);
        $priceChurch = MoneyHelper::toFloat($rawPriceChurch);
        $discount = MoneyHelper::toFloat($rawDiscount);

        if ($price === null && $priceChurch === null && $discount === null) {
            return null;
        }

        $total = ($price ?? 0.0) + ($priceChurch ?? 0.0) - ($discount ?? 0.0);

        return MoneyHelper::format_money($total);
    }

    public function pixKeyForPayment(): string
    {
        $pixKey = trim((string) ($this->pix_key ?? ''));

        if ($pixKey !== '') {
            return $pixKey;
        }

        return self::DEFAULT_PIX_KEY;
    }

    public function pixQrCodeUrlForPayment(): string
    {
        $pixQrCodePath = trim((string) ($this->pix_qr_code ?? ''));

        if ($pixQrCodePath !== '' && Storage::disk('public')->exists($pixQrCodePath)) {
            return Storage::disk('public')->url($pixQrCodePath);
        }

        return asset(self::DEFAULT_PIX_QR_CODE_ASSET_PATH);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function vourses(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function eventDates(): HasMany
    {
        return $this->hasMany(EventDate::class, 'training_id');
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(TrainingScheduleItem::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'training_user')
            ->withPivot('accredited', 'kit', 'payment', 'payment_receipt');
    }

    public function newChurches(): HasMany
    {
        return $this->hasMany(TrainingNewChurch::class);
    }

    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mentors')
            ->withPivot('created_by')
            ->withTimestamps();
    }

    public function mentorsPivot(): HasMany
    {
        return $this->hasMany(Mentor::class);
    }

    public function stpSessions(): HasMany
    {
        return $this->hasMany(StpSession::class);
    }

    public function financeAudits(): HasMany
    {
        return $this->hasMany(TrainingFinanceAudit::class);
    }
}
