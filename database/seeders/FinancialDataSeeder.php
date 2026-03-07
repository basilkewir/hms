<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use App\Models\Setting;
use Carbon\Carbon;

class FinancialDataSeeder extends Seeder
{
    public function run()
    {
        // Set currency settings
        Setting::updateOrCreate(
            ['key' => 'currency_code'],
            ['value' => 'USD', 'group' => 'financial']
        );
        
        Setting::updateOrCreate(
            ['key' => 'currency_symbol'],
            ['value' => '$', 'group' => 'financial']
        );
        
        Setting::updateOrCreate(
            ['key' => 'currency_position'],
            ['value' => 'before', 'group' => 'financial']
        );

        // Create expense categories
        $categories = [
            ['name' => 'Payroll', 'code' => 'PAYROLL', 'description' => 'Employee salaries and wages'],
            ['name' => 'Food Costs', 'code' => 'FOOD_COSTS', 'description' => 'Cost of food and beverages'],
            ['name' => 'Room Supplies', 'code' => 'ROOM_SUPPLIES', 'description' => 'Room amenities and supplies'],
            ['name' => 'Laundry', 'code' => 'LAUNDRY', 'description' => 'Laundry and cleaning services'],
            ['name' => 'Utilities', 'code' => 'UTILITIES', 'description' => 'Electricity, water, gas'],
            ['name' => 'Marketing', 'code' => 'MARKETING', 'description' => 'Marketing and advertising'],
            ['name' => 'Administration', 'code' => 'ADMIN', 'description' => 'Administrative expenses'],
            ['name' => 'Maintenance', 'code' => 'MAINTENANCE', 'description' => 'Maintenance and repairs'],
            ['name' => 'Insurance', 'code' => 'INSURANCE', 'description' => 'Insurance premiums'],
            ['name' => 'Professional Services', 'code' => 'PROFESSIONAL', 'description' => 'Legal, accounting, consulting'],
            ['name' => 'Depreciation', 'code' => 'DEPRECIATION', 'description' => 'Asset depreciation'],
            ['name' => 'Interest Income', 'code' => 'INTEREST_INCOME', 'description' => 'Interest earned'],
            ['name' => 'Interest Expense', 'code' => 'INTEREST_EXPENSE', 'description' => 'Interest paid'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['code' => $category['code']],
                $category + ['is_active' => true]
            );
        }

        // Create sample expenses for the current month
        $this->createSampleExpenses();

        // Create sample revenue data (simplified without foreign key constraints)
        $this->createSimpleRevenueData();
    }

    private function createSampleExpenses()
    {
        // Check if expenses already exist
        if (Expense::count() > 50) { // Allow some existing expenses but create more for better forecast
            return;
        }

        $categories = ExpenseCategory::all()->keyBy('code');
        
        // Create expenses for the past 6 months to provide good forecast data
        for ($monthsAgo = 6; $monthsAgo >= 0; $monthsAgo--) {
            $monthDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            
            $expenses = [
                ['category' => 'PAYROLL', 'amount' => 95000 + rand(-5000, 5000), 'description' => 'Monthly payroll'],
                ['category' => 'FOOD_COSTS', 'amount' => 35000 + rand(-3000, 3000), 'description' => 'Food and beverage costs'],
                ['category' => 'ROOM_SUPPLIES', 'amount' => 25000 + rand(-2000, 2000), 'description' => 'Room amenities and supplies'],
                ['category' => 'LAUNDRY', 'amount' => 15000 + rand(-1000, 1000), 'description' => 'Laundry services'],
                ['category' => 'UTILITIES', 'amount' => 45000 + rand(-3000, 3000), 'description' => 'Monthly utilities'],
                ['category' => 'MARKETING', 'amount' => 25000 + rand(-5000, 5000), 'description' => 'Digital marketing campaign'],
                ['category' => 'ADMIN', 'amount' => 18000 + rand(-2000, 2000), 'description' => 'Administrative expenses'],
                ['category' => 'MAINTENANCE', 'amount' => 15000 + rand(-3000, 3000), 'description' => 'Equipment maintenance'],
                ['category' => 'INSURANCE', 'amount' => 12000 + rand(-1000, 1000), 'description' => 'Monthly insurance premium'],
                ['category' => 'PROFESSIONAL', 'amount' => 8000 + rand(-1000, 1000), 'description' => 'Legal and accounting'],
                ['category' => 'DEPRECIATION', 'amount' => 12000 + rand(-500, 500), 'description' => 'Monthly depreciation'],
                ['category' => 'INTEREST_EXPENSE', 'amount' => 7000 + rand(-500, 500), 'description' => 'Loan interest'],
            ];

            foreach ($expenses as $index => $expenseData) {
                $category = $categories[$expenseData['category']];
                
                // Add some seasonal variation
                $seasonalMultiplier = 1.0;
                if ($monthDate->month >= 6 && $monthDate->month <= 8) {
                    $seasonalMultiplier = 1.1; // Summer months slightly higher
                } elseif ($monthDate->month >= 11 || $monthDate->month <= 1) {
                    $seasonalMultiplier = 1.15; // Holiday season higher
                } elseif ($monthDate->month >= 2 && $monthDate->month <= 4) {
                    $seasonalMultiplier = 0.9; // Late winter/early spring lower
                }
                
                $adjustedAmount = round($expenseData['amount'] * $seasonalMultiplier);
                
                Expense::create([
                    'expense_number' => 'EXP-' . $monthDate->format('Y') . '-' . str_pad(($monthsAgo * 100 + $index + 1), 4, '0', STR_PAD_LEFT),
                    'expense_category_id' => $category->id,
                    'vendor_name' => $this->getVendorName($expenseData['category']),
                    'description' => $expenseData['description'] . ' (' . $monthDate->format('F Y') . ')',
                    'expense_date' => $monthDate->copy()->addDays(rand(1, 28)),
                    'amount' => $adjustedAmount,
                    'currency' => 'USD',
                    'payment_method' => 'bank_transfer',
                    'status' => 'approved',
                    'submitted_by' => 1,
                    'approved_by' => 1,
                    'approved_at' => $monthDate->copy()->addDays(rand(1, 5)),
                ]);
            }
        }
    }

    private function createSimpleRevenueData()
    {
        // For now, just create some basic settings and let the financial service
        // calculate from existing data. We'll add more complex revenue data later
        // when we have proper reservations and guests set up.

        // Add some additional settings for financial reporting
        Setting::updateOrCreate(
            ['key' => 'hotel_name'],
            ['value' => 'Grand Hotel Management System', 'group' => 'general']
        );

        Setting::updateOrCreate(
            ['key' => 'fiscal_year_start'],
            ['value' => '01-01', 'group' => 'financial']
        );

        Setting::updateOrCreate(
            ['key' => 'tax_rate'],
            ['value' => '10.00', 'group' => 'financial']
        );

        // Add Xtream Codes API settings (placeholder values)
        Setting::updateOrCreate(
            ['key' => 'xtream_api_url'],
            ['value' => 'http://your-xtream-server.com:8080', 'group' => 'iptv', 'description' => 'Xtream Codes API URL']
        );

        Setting::updateOrCreate(
            ['key' => 'xtream_username'],
            ['value' => 'your_username', 'group' => 'iptv', 'description' => 'Xtream Codes Username']
        );

        Setting::updateOrCreate(
            ['key' => 'xtream_password'],
            ['value' => 'your_password', 'group' => 'iptv', 'description' => 'Xtream Codes Password']
        );
    }

    private function getVendorName($category)
    {
        $vendors = [
            'PAYROLL' => 'Internal Payroll',
            'FOOD_COSTS' => 'Food Suppliers Inc',
            'ROOM_SUPPLIES' => 'Hotel Supplies Co',
            'LAUNDRY' => 'Clean Laundry Services',
            'UTILITIES' => 'City Utilities',
            'MARKETING' => 'Digital Marketing Agency',
            'ADMIN' => 'Office Supplies Store',
            'MAINTENANCE' => 'Maintenance Services LLC',
            'INSURANCE' => 'Insurance Company',
            'PROFESSIONAL' => 'Legal & Accounting Firm',
            'DEPRECIATION' => 'Internal Accounting',
            'INTEREST_EXPENSE' => 'Bank of Commerce',
        ];

        return $vendors[$category] ?? 'Unknown Vendor';
    }

    private function getServiceDescription($chargeCode)
    {
        $descriptions = [
            'FOOD' => 'Restaurant services',
            'CONFERENCE' => 'Conference room rental',
            'OTHER' => 'Additional services',
        ];

        return $descriptions[$chargeCode] ?? 'Service charge';
    }
}
