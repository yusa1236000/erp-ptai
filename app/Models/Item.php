<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UnitOfMeasure;
use App\Models\ItemCategory;
use App\Models\CurrencyRate;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $primaryKey = 'item_id';
    
    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category_id',
        'uom_id',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'is_purchasable',
        'is_sellable',
        'cost_price',
        'sale_price',
        'cost_price_currency', // Baru
        'sale_price_currency', // Baru
        'length',
        'width',
        'thickness',
        'weight',
        'document_path',
    ];

    protected $casts = [
        'current_stock' => 'float',
        'minimum_stock' => 'float',
        'maximum_stock' => 'float',
        'is_purchasable' => 'boolean',
        'is_sellable' => 'boolean',
        'cost_price' => 'float',
        'sale_price' => 'float',
        'length' => 'float',
        'width' => 'float',
        'thickness' => 'float',
        'weight' => 'float',
    ];

    // Existing relationships remain unchanged
    
    /**
     * Get default purchase price for this item in specific currency.
     * 
     * @param string $currencyCode
     * @param string|null $date
     * @return float
     */
    public function getDefaultPurchasePriceInCurrency($currencyCode, $date = null)
    {
        // If already in requested currency
        if ($this->cost_price_currency === $currencyCode) {
            return $this->cost_price;
        }
        
        // Get exchange rate
        $rate = CurrencyRate::getCurrentRate($this->cost_price_currency, $currencyCode, $date);
        
        if (!$rate) {
            // Return original price if no rate available
            return $this->cost_price;
        }
        
        return $this->cost_price * $rate;
    }
    
    /**
     * Get default sale price for this item in specific currency.
     * 
     * @param string $currencyCode
     * @param string|null $date
     * @return float
     */
    public function getDefaultSalePriceInCurrency($currencyCode, $date = null)
    {
        // If already in requested currency
        if ($this->sale_price_currency === $currencyCode) {
            return $this->sale_price;
        }
        
        // Get exchange rate
        $rate = CurrencyRate::getCurrentRate($this->sale_price_currency, $currencyCode, $date);
        
        if (!$rate) {
            // Return original price if no rate available
            return $this->sale_price;
        }
        
        return $this->sale_price * $rate;
    }
    
    /**
     * Get the best purchase price for a specific vendor and quantity in specified currency.
     * 
     * @param int|null $vendorId
     * @param float $quantity
     * @param string $currencyCode
     * @param string|null $date
     * @return float
     */
    public function getBestPurchasePriceInCurrency($vendorId = null, $quantity = 1, $currencyCode = null, $date = null)
    {
        $currencyCode = $currencyCode ?? config('app.base_currency', 'USD');
        $date = $date ?? now()->format('Y-m-d');
        
        // First try to find a vendor-specific price for the given quantity in requested currency
        if ($vendorId) {
            $vendorPrice = $this->purchasePrices()
                ->active()
                ->where('vendor_id', $vendorId)
                ->where('min_quantity', '<=', $quantity)
                ->where('currency_code', $currencyCode)
                ->orderBy('price', 'asc')
                ->orderBy('min_quantity', 'desc')
                ->first();
                
            if ($vendorPrice) {
                return $vendorPrice->price;
            }
            
            // Try to find vendor-specific price in any currency and convert
            $anyVendorPrice = $this->purchasePrices()
                ->active()
                ->where('vendor_id', $vendorId)
                ->where('min_quantity', '<=', $quantity)
                ->orderBy('price', 'asc')
                ->orderBy('min_quantity', 'desc')
                ->first();
                
            if ($anyVendorPrice) {
                return $anyVendorPrice->getPriceInCurrency($currencyCode, $date);
            }
        }
        
        // Next try to find a general purchase price in requested currency
        $generalPrice = $this->purchasePrices()
            ->active()
            ->whereNull('vendor_id')
            ->where('min_quantity', '<=', $quantity)
            ->where('currency_code', $currencyCode)
            ->orderBy('price', 'asc')
            ->orderBy('min_quantity', 'desc')
            ->first();
            
        if ($generalPrice) {
            return $generalPrice->price;
        }
        
        // Try to find any general price and convert
        $anyGeneralPrice = $this->purchasePrices()
            ->active()
            ->whereNull('vendor_id')
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('price', 'asc')
            ->orderBy('min_quantity', 'desc')
            ->first();
            
        if ($anyGeneralPrice) {
            return $anyGeneralPrice->getPriceInCurrency($currencyCode, $date);
        }
        
        // If no price found, return the default cost price in requested currency
        return $this->getDefaultPurchasePriceInCurrency($currencyCode, $date);
    }
    
    /**
     * Get the best sale price for a specific customer and quantity in specified currency.
     * 
     * @param int|null $customerId
     * @param float $quantity
     * @param string $currencyCode
     * @param string|null $date
     * @return float
     */
    public function getBestSalePriceInCurrency($customerId = null, $quantity = 1, $currencyCode = null, $date = null)
    {
        $currencyCode = $currencyCode ?? config('app.base_currency', 'USD');
        $date = $date ?? now()->format('Y-m-d');
        
        // First try to find a customer-specific price for the given quantity in requested currency
        if ($customerId) {
            $customerPrice = $this->salePrices()
                ->active()
                ->where('customer_id', $customerId)
                ->where('min_quantity', '<=', $quantity)
                ->where('currency_code', $currencyCode)
                ->orderBy('price', 'asc')
                ->orderBy('min_quantity', 'desc')
                ->first();
                
            if ($customerPrice) {
                return $customerPrice->price;
            }
            
            // Try to find customer-specific price in any currency and convert
            $anyCustomerPrice = $this->salePrices()
                ->active()
                ->where('customer_id', $customerId)
                ->where('min_quantity', '<=', $quantity)
                ->orderBy('price', 'asc')
                ->orderBy('min_quantity', 'desc')
                ->first();
                
            if ($anyCustomerPrice) {
                return $anyCustomerPrice->getPriceInCurrency($currencyCode, $date);
            }
        }
        
        // Next try to find a general sale price in requested currency
        $generalPrice = $this->salePrices()
            ->active()
            ->whereNull('customer_id')
            ->where('min_quantity', '<=', $quantity)
            ->where('currency_code', $currencyCode)
            ->orderBy('price', 'asc')
            ->orderBy('min_quantity', 'desc')
            ->first();
            
        if ($generalPrice) {
            return $generalPrice->price;
        }
        
        // Try to find any general price and convert
        $anyGeneralPrice = $this->salePrices()
            ->active()
            ->whereNull('customer_id')
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('price', 'asc')
            ->orderBy('min_quantity', 'desc')
            ->first();
            
        if ($anyGeneralPrice) {
            return $anyGeneralPrice->getPriceInCurrency($currencyCode, $date);
        }
        
        // If no price found, return the default sale price in requested currency
        return $this->getDefaultSalePriceInCurrency($currencyCode, $date);
    }
}