<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportOtherDebt extends Model
{
    protected $fillable = [
        'subscription_report_id',
        'entity',
        'currency',
        'amount',
        'expiration_days',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function subscriptionReport(): BelongsTo
    {
        return $this->belongsTo(SubscriptionReport::class);
    }
}
