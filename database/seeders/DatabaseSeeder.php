<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\MonthlyFund;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@ragingdevelopers.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => 'admin',
                'status' => 'active',
                'phone' => null,
            ]
        );

        $monthDate = now()->startOfMonth()->toDateString();

        MonthlyFund::updateOrCreate(
            ['fund_date' => $monthDate, 'note' => 'Owner company fund'],
            [
                'month_date' => $monthDate,
                'amount' => 2500,
                'created_by' => $admin->id,
            ]
        );

        for ($i = 1; $i <= 8; $i++) {
            $employee = User::updateOrCreate(
                ['email' => "employee{$i}@ragingdevelopers.com"],
                [
                    'name' => "Employee {$i}",
                    'password' => 'password',
                    'role' => 'employee',
                    'status' => 'active',
                    'phone' => '900000000'.$i,
                ]
            );

            foreach (['Tea', 'Snacks', 'Food'] as $index => $title) {
                Expense::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'title' => $title,
                        'expense_date' => Carbon::now()->startOfMonth()->addDays($index + $i)->toDateString(),
                    ],
                    [
                        'description' => $title.' expense for current month',
                        'amount' => 40 + ($i * 10) + ($index * 25),
                        'month_date' => $monthDate,
                        'status' => 'approved',
                    ]
                );
            }
        }
    }
}
