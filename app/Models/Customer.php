<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'birth_date',
        'points',
        'total_spent',
        'total_transactions',
        'last_purchase_at',
        'last_birthday_used_at',
        'notes',
    ];

    /**
     * @return array{birth_date: string, points: string, total_spent: string, total_transactions: string, last_purchase_at: string, last_birthday_used_at: string}
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'points' => 'integer',
            'total_spent' => 'decimal:2',
            'total_transactions' => 'integer',
            'last_purchase_at' => 'datetime',
            'last_birthday_used_at' => 'datetime',
        ];
    }

    public function pointLogs(): HasMany
    {
        return $this->hasMany(PointLog::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope to search customers by name or phone.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
