<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_amount', 'interest_rate', 'repayment_plan', 'customer_id', 'start_date', 'end_date', 'total_amount_due',      // <-- ADD this line
        'monthly_installment',   // <-- ADD this line
        'remaining_balance',
    ];

    protected $dates = ['start_date', 'end_date'];

    protected $attributes = [
        'start_date' => 'now',  // This is not recommended but shows possible approach
    ];

    // Set start_date automatically if not provided
    protected static function booted()
    {
        static::creating(function ($loan) {
            if (empty($loan->start_date)) {
                $loan->start_date = now();
            }
        });
    }

    public function customer()
{
    return $this->belongsTo(Customer::class);
}

public function repayments()
{
    return $this->hasMany(Repayment::class);
}

public function notifications()
{
    return $this->hasMany(Notification::class);
}

}
