<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCreditCard extends Model
{
    protected $fillable = [
        'subscription_report_id',
        'bank',
        'currency',
        'line',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'line' => 'decimal:2',
            'used' => 'decimal:2',
        ];
    }

    public function subscriptionReport(): BelongsTo
    {
        return $this->belongsTo(SubscriptionReport::class);
    }
}
