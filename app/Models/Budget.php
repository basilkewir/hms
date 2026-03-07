<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'start_date',
        'end_date',
        'category_id',
        'department_id',
        'status',
        'created_by',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
        'status' => 'string',
    ];

    // Budget statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_ARCHIVED = 'archived';

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function expenses()
    {
        return $this->hasMany(BudgetExpense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function getSpentAmountAttribute()
    {
        return Expense::where('expense_category_id', $this->category_id)
            ->whereBetween('expense_date', [$this->start_date, $this->end_date])
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->spent_amount;
    }

    public function getUtilizationPercentageAttribute()
    {
        if ($this->amount == 0) {
            return $this->spent_amount > 0 ? 100 : 0;
        }
        return round(($this->spent_amount / $this->amount) * 100, 2);
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case self::STATUS_APPROVED:
                return 'green';
            case self::STATUS_PENDING_APPROVAL:
                return 'yellow';
            case self::STATUS_REJECTED:
                return 'red';
            case self::STATUS_DRAFT:
                return 'gray';
            case self::STATUS_EXPIRED:
                return 'orange';
            default:
                return 'gray';
        }
    }

    public function isOverBudget()
    {
        return $this->spent_amount > $this->amount;
    }

    public function isNearBudget()
    {
        return $this->utilization_percentage >= 80 && $this->utilization_percentage < 100;
    }

    public function isOnTrack()
    {
        return $this->utilization_percentage < 80;
    }

    public function updateStatus()
    {
        if ($this->status === self::STATUS_APPROVED && $this->end_date < now()) {
            $this->update(['status' => self::STATUS_EXPIRED]);
        }
    }

    // Budget monitoring and alerts
    public function getAlertThresholds()
    {
        return [
            'warning' => 75, // Send warning at 75% utilization
            'critical' => 90, // Send critical alert at 90% utilization
            'over_budget' => 100 // Alert when over budget
        ];
    }

    public function shouldSendWarningAlert()
    {
        return $this->is_active && $this->utilization_percentage >= $this->getAlertThresholds()['warning'];
    }

    public function shouldSendCriticalAlert()
    {
        return $this->is_active && $this->utilization_percentage >= $this->getAlertThresholds()['critical'];
    }

    public function shouldSendOverBudgetAlert()
    {
        return $this->is_active && $this->utilization_percentage >= $this->getAlertThresholds()['over_budget'];
    }

    public function getDaysRemaining()
    {
        if ($this->end_date < now()) {
            return 0;
        }
        return $this->end_date->diffInDays(now());
    }

    public function isNearExpiration()
    {
        return $this->getDaysRemaining() <= 7 && $this->getDaysRemaining() > 0;
    }

    public function getBudgetHealth()
    {
        if ($this->isOverBudget()) {
            return 'critical';
        } elseif ($this->isNearBudget()) {
            return 'warning';
        } elseif ($this->isOnTrack()) {
            return 'good';
        }
        return 'unknown';
    }

    // Budget analytics
    public function getMonthlyUtilization()
    {
        $monthlyData = [];
        $startDate = $this->start_date->copy();
        $endDate = $this->end_date->copy();

        while ($startDate->lte($endDate)) {
            $monthEnd = $startDate->copy()->endOfMonth();
            if ($monthEnd->gt($endDate)) {
                $monthEnd = $endDate->copy();
            }

            $monthSpent = Expense::where('expense_category_id', $this->category_id)
                ->whereBetween('expense_date', [$startDate, $monthEnd])
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');

            $monthBudget = $this->amount * ($monthEnd->diffInDays($startDate) + 1) / $this->start_date->diffInDays($this->end_date) + 1;

            $monthlyData[] = [
                'month' => $startDate->format('F Y'),
                'month_start' => $startDate->format('Y-m-d'),
                'month_end' => $monthEnd->format('Y-m-d'),
                'budgeted' => $monthBudget,
                'spent' => $monthSpent,
                'remaining' => $monthBudget - $monthSpent,
                'utilization' => $monthBudget > 0 ? round(($monthSpent / $monthBudget) * 100, 2) : 0
            ];

            $startDate->addMonth()->startOfMonth();
        }

        return $monthlyData;
    }

    public function getExpenseBreakdown()
    {
        return Expense::where('expense_category_id', $this->category_id)
            ->whereBetween('expense_date', [$this->start_date, $this->end_date])
            ->whereIn('status', ['approved', 'paid'])
            ->selectRaw('expense_type, SUM(amount) as total_amount, COUNT(*) as transaction_count')
            ->groupBy('expense_type')
            ->orderBy('total_amount', 'desc')
            ->get();
    }

    public function getVarianceAnalysis()
    {
        $plannedDailyRate = $this->amount / $this->start_date->diffInDays($this->end_date) + 1;
        $daysElapsed = now()->diffInDays($this->start_date);
        $daysRemaining = $this->getDaysRemaining();

        $plannedSpendToDate = $plannedDailyRate * $daysElapsed;
        $actualSpendToDate = $this->spent_amount;

        return [
            'planned_daily_rate' => $plannedDailyRate,
            'days_elapsed' => $daysElapsed,
            'days_remaining' => $daysRemaining,
            'planned_spend_to_date' => $plannedSpendToDate,
            'actual_spend_to_date' => $actualSpendToDate,
            'variance' => $actualSpendToDate - $plannedSpendToDate,
            'variance_percentage' => $plannedSpendToDate > 0 ? round((($actualSpendToDate - $plannedSpendToDate) / $plannedSpendToDate) * 100, 2) : 0,
            'projected_overrun' => $this->calculateProjectedOverrun()
        ];
    }

    private function calculateProjectedOverrun()
    {
        if ($this->utilization_percentage == 0) {
            return 0;
        }

        $daysElapsed = now()->diffInDays($this->start_date);
        $totalDays = $this->start_date->diffInDays($this->end_date) + 1;

        if ($daysElapsed == 0) {
            return 0;
        }

        $dailySpendRate = $this->spent_amount / $daysElapsed;
        $projectedTotalSpend = $dailySpendRate * $totalDays;

        return max(0, $projectedTotalSpend - $this->amount);
    }
}
