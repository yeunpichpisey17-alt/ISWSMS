<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'sku',
        'barcode',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'min_stock_level',
        'unit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_quantity <= $this->min_stock_level,
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public static function generateSku(Category $category): string
    {
        $prefix = strtoupper(substr($category->name, 0, 4));
        $lastProduct = self::where('sku', 'like', $prefix.'-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($lastProduct) {
            $parts = explode('-', $lastProduct->sku);
            $nextNumber = (int) end($parts) + 1;
        }

        return $prefix.'-'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
