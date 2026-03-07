<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\PosTransaction;
use App\Models\FolioCharge;
use App\Models\Expense;

class TestTransactionData extends Command
{
    protected $signature = 'test:transactions';
    protected $description = 'Test transaction data in database';

    public function handle()
    {
        $this->info('=== PAYMENTS ===');
        $payments = Payment::limit(3)->get();
        $this->info('Count: ' . Payment::count());
        foreach ($payments as $p) {
            $this->line("ID: {$p->id}, Amount: {$p->amount}, Status: {$p->status}, Processed: {$p->processed_at}");
        }

        $this->info('\n=== SALES ===');
        $sales = Sale::limit(3)->get();
        $this->info('Count: ' . Sale::count());
        foreach ($sales as $s) {
            $this->line("ID: {$s->id}, Amount: {$s->total_amount}, Payment Status: {$s->payment_status}, Created: {$s->created_at}");
        }

        $this->info('\n=== POS TRANSACTIONS ===');
        $posTransactions = PosTransaction::limit(3)->get();
        $this->info('Count: ' . PosTransaction::count());
        foreach ($posTransactions as $pt) {
            $this->line("ID: {$pt->id}, Amount: {$pt->amount}, Type: {$pt->type}, Created: {$pt->created_at}");
        }

        $this->info('\n=== FOLIO CHARGES ===');
        $folioCharges = FolioCharge::limit(3)->get();
        $this->info('Count: ' . FolioCharge::count());
        foreach ($folioCharges as $fc) {
            $this->line("ID: {$fc->id}, Net Amount: {$fc->net_amount}, Created: {$fc->created_at}");
        }

        $this->info('\n=== EXPENSES ===');
        $expenses = Expense::limit(3)->get();
        $this->info('Count: ' . Expense::count());
        foreach ($expenses as $e) {
            $this->line("ID: {$e->id}, Amount: {$e->amount}, Status: {$e->status}, Date: {$e->expense_date}");
        }

        return 0;
    }
}
