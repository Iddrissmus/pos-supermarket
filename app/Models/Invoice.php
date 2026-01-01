<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Invoice extends Model
{
    use HasUuids;

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['uuid'];
    }

    protected $fillable = [
        'uuid',
        'invoice_number',
        'customer_id',
        'customer_email',
        'customer_phone',
        'branch_id',
        'created_by',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'payment_terms',
        'notes',
        'terms_conditions',
        'sent_at',
        'paid_at',
        'payment_link_token',
        'is_recurring',
        'recurring_frequency',
        'recurring_end_date',
        'recurring_next_date',
        'allow_partial_payment',
        'parent_invoice_id',
        'scheduled_send_date'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'scheduled_send_date' => 'datetime',
        'recurring_end_date' => 'date',
        'recurring_next_date' => 'date',
        'is_recurring' => 'boolean',
        'allow_partial_payment' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) return 0;
        return $this->due_date->diffInDays(now());
    }

    public function parentInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function childInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }
    


    public function getStatusColorAttribute(): string
    {
        if ($this->status !== 'paid' && $this->paid_amount > 0 && $this->balance_due > 0) {
            return 'orange'; // Partial Payment
        }

        return match($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status !== 'paid' && $this->paid_amount > 0 && $this->balance_due > 0) {
            return 'Partial';
        }
        return ucfirst($this->status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
                     ->where('due_date', '<', now());
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['sent', 'overdue'])
                     ->where('balance_due', '>', 0);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('line_total');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->balance_due = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }
    
    public function scopeActiveRecurring($query)
    {
        return $query->where('is_recurring', true)
                     ->where(function($q) {
                         $q->whereNull('recurring_end_date')
                           ->orWhere('recurring_end_date', '>=', now()->toDateString());
                     });
    }

    // ...

    public function markAsPaid(float $amount = null): void
    {
        $currentPaid = $this->paid_amount;
        $toPay = $amount ?? $this->balance_due;
        
        $newPaidTotal = $currentPaid + $toPay;
        $newBalance = $this->total_amount - $newPaidTotal;
        
        // Ensure strictly non-negative balance (handle overpayment if needed, for now clamp)
        if ($newBalance < 0) $newBalance = 0;

        $updateData = [
            'paid_amount' => $newPaidTotal,
            'balance_due' => $newBalance,
            'paid_at' => now()
        ];
        
        if ($newBalance <= 0) {
            $updateData['status'] = 'paid';
        } else {
            // Partial payment - status remains 'sent' or 'overdue' basically
            // But we can ensure it's not 'draft'
            if ($this->status === 'draft') {
                $updateData['status'] = 'sent';
            }
        }

        $this->update($updateData);
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastInvoice = static::whereDate('created_at', now())
            ->where('invoice_number', 'like', $prefix . $date . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
