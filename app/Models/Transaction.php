<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'transaction_number',
        'date',
        'channel',
        'cashier_id',
        'customer_id',
        'subtotal',
        'discount',
        'member_discount',
        'birthday_discount',
        'points_used',
        'point_discount',
        'points_earned',
        'total',
        'payment_method',
        'payment_amount',
        'change_amount',
        'notes',
    ];

    /**
     * @return array{date: string, subtotal: string, discount: string, member_discount: string, birthday_discount: string, points_used: string, point_discount: string, points_earned: string, total: string, payment_amount: string, change_amount: string}
     */
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'member_discount' => 'decimal:2',
            'birthday_discount' => 'decimal:2',
            'points_used' => 'integer',
            'point_discount' => 'decimal:2',
            'points_earned' => 'integer',
            'total' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function pointLogs(): HasMany
    {
        return $this->hasMany(PointLog::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
