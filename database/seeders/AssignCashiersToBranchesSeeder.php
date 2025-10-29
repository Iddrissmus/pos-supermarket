<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class AssignCashiersToBranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get cashiers
        $cashier1 = User::where('email', 'cashier@pos.com')->first();
        $cashier2 = User::where('email', 'cashier2@pos.com')->first();

        // Get branches
        $mainBranch = Branch::where('name', 'Main Branch')->first();
        $downtownBranch = Branch::where('name', 'Downtown Branch')->first();

        // Assign cashiers to branches
        if ($cashier1 && $mainBranch) {
            $cashier1->update(['branch_id' => $mainBranch->id]);
        }

        if ($cashier2 && $downtownBranch) {
            $cashier2->update(['branch_id' => $downtownBranch->id]);
        }

        $this->command->info('✓ Cashiers assigned to branches successfully');
    }
}
