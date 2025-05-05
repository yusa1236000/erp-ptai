<?php
// app/Enums/PaymentTerm.php
namespace App\Enums;

use App\Models\Sales\Customer;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;

class PaymentTerm
{
    const THIRTY_DAYS = 30;
    const SIXTY_DAYS = 60;
    const NINETY_DAYS = 90;
    
    public static function getValidTerms()
    {
        return [self::THIRTY_DAYS, self::SIXTY_DAYS, self::NINETY_DAYS];
    }
}