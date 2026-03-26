<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_number',
        'customer_id',
        'product_id',
        'serial_number',
        'device_name',
        'device_brand',
        'device_model',
        'issue_description',
        'diagnosis',
        'resolution',
        'status',
        'priority',
        'estimated_cost',
        'actual_cost',
        'assigned_to',
        'received_by',
        'estimated_completion',
        'completed_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'estimated_completion' => 'date',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(RepairPart::class);
    }

    public function warrantyClaim(): HasOne
    {
        return $this->hasOne(WarrantyClaim::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public static function generateRepairNumber(): string
    {
        $prefix = 'REP-' . now()->format('Ymd');
        $last = self::where('repair_number', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->repair_number);
            $nextNumber = (int) end($parts) + 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
