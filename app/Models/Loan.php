<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    protected $appends = ['due_dates'];


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

public function getDueDatesAttribute()
{
    $dueDates = [];
    $startDate = \Carbon\Carbon::parse($this->start_date);
    $installmentAmount = $this->monthly_installment;
    $totalDue = $this->total_amount_due;
    $remainingBalance = $this->remaining_balance;

    for ($i = 0; $i <= $this->repayment_plan; $i++) {
        $dueDate = $startDate->copy()->addMonths($i + 1);

        // Expected remaining balance after paying all previous installments
        $expectedRemaining = $totalDue - (($i + 1) * $installmentAmount);

        // Outstanding if the actual remaining balance is greater than expected
        $outstanding = $remainingBalance > $expectedRemaining;

        // Calculate the outstanding amount
        $outstandingBalance = 0;
        if ($outstanding) {
            $outstandingBalance = $remainingBalance - $expectedRemaining;
        }

        if($outstandingBalance > $installmentAmount){
            $outstandingBalance = $installmentAmount;
        }
        if ($outstandingBalance < 0) {
            $outstandingBalance = 0;
        }

        // cast outstanding balance to number
        $outstandingBalance = number_format($outstandingBalance, 2, '.', '');

        $dueDates[] = [
            'date' => $dueDate->format('Y-m-d'),
            'balance' => $outstandingBalance,
            'outstanding' => $outstanding
        ];
    }

    return $dueDates;
}

}
