<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_ENTRY = 'entry';

    public const TYPE_EXIT = 'exit';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_LOSS = 'loss';

    public const TYPE_KIT_COMPONENT_EXIT = 'kit_component_exit';

    protected $fillable = [
        'inventory_id',
        'material_id',
        'user_id',
        'training_id',
        'movement_type',
        'quantity',
        'balance_after',
        'batch_uuid',
        'notes',
        'reference_type',
        'reference_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inventory_id' => 'integer',
            'material_id' => 'integer',
            'user_id' => 'integer',
            'training_id' => 'integer',
            'quantity' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function typeLabel(): string
    {
        return match ($this->movement_type) {
            self::TYPE_ENTRY => __('Entrada'),
            self::TYPE_EXIT => __('Saída'),
            self::TYPE_TRANSFER_IN => __('Transferência recebida'),
            self::TYPE_TRANSFER_OUT => __('Transferência enviada'),
            self::TYPE_ADJUSTMENT => __('Ajuste'),
            self::TYPE_LOSS => __('Perda/Avaria'),
            self::TYPE_KIT_COMPONENT_EXIT => __('Baixa de componente'),
            default => (string) $this->movement_type,
        };
    }

    public function typeBadgeClasses(): string
    {
        return match ($this->movement_type) {
            self::TYPE_ENTRY, self::TYPE_TRANSFER_IN => 'bg-emerald-100 text-emerald-800',
            self::TYPE_EXIT, self::TYPE_TRANSFER_OUT, self::TYPE_KIT_COMPONENT_EXIT => 'bg-amber-100 text-amber-800',
            self::TYPE_LOSS => 'bg-rose-100 text-rose-700',
            self::TYPE_ADJUSTMENT => 'bg-sky-100 text-sky-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
