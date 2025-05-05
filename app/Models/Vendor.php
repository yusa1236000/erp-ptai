<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'vendors';
    protected $primaryKey = 'vendor_id';
    protected $fillable = [
        'vendor_code',
        'name',
        'address',
        'tax_id',
        'contact_person',
        'phone',
        'email',
        'preferred_currency', // Baru
        'payment_term', // Baru
        'status'
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id');
    }

    public function quotations()
    {
        return $this->hasMany(VendorQuotation::class, 'vendor_id');
    }

    public function contracts()
    {
        return $this->hasMany(VendorContract::class, 'vendor_id');
    }

    public function evaluations()
    {
        return $this->hasMany(VendorEvaluation::class, 'vendor_id');
    }

    public function invoices()
    {
        return $this->hasMany(VendorInvoice::class, 'vendor_id');
    }
    
    /**
     * Get the payables for the vendor.
     */
    public function payables()
    {
        return $this->hasMany(VendorPayable::class, 'vendor_id');
    }

    public function getPaymentTermDescriptionAttribute()
    {
        switch($this->payment_term) {
            case 30:
                return 'Net 30 days';
            case 60:
                return 'Net 60 days';
            case 90:
                return 'Net 90 days';
            default:
                return 'Net ' . $this->payment_term . ' days';
        }
    }
}