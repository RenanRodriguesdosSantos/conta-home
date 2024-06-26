<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'total',
        'description'
    ];

    protected $append = [
        'status'
    ];

    protected static function booted(): void
    {
        static::created(function (Expense $expense) {
            $value = ($expense->total / User::count()) ?? 0;

            User::all()
                ->each(function (User $user) use ($expense, $value) {
                    $expense->payments()->create([
                        'user_id' => $user->id,
                        'value' => $value,
                        'paid_at' => auth()->id() == $user->id ? now() : null,
                        'status' => auth()->id() == $user->id ? PaymentStatus::PAID : PaymentStatus::PENDING
                    ]);
                });
        });
    }

    public function payments() : HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute()
    {
        return $this->payments()
            ->status(PaymentStatus::PENDING)
            ->count() > 0 ? PaymentStatus::PENDING : PaymentStatus::PAID;
    }

    public function scopeStatus(Builder $query, $status) : Builder
    {
        switch ($status) {
            case PaymentStatus::PAID:
                return $query->whereDoesntHave('payments', fn ($query) => $query->status(PaymentStatus::PENDING));
                break;

            case PaymentStatus::PENDING:
                $query->whereHas('payments', fn ($query) => $query->status(PaymentStatus::PENDING));
                break;
            
            default:
                break;
        }
        return $query;
    }
}
