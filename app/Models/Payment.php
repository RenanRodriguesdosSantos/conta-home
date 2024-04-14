<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'status',
        'paid_at',
        'expense_id',
        'user_id'
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
        ];
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expense() : BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function scopeStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }
}
