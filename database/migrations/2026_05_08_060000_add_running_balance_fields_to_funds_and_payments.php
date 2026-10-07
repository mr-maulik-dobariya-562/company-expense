<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_funds', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_funds', 'fund_date')) {
                $table->date('fund_date')->nullable()->after('id')->index();
            }
        });

        DB::table('monthly_funds')
            ->whereNull('fund_date')
            ->update(['fund_date' => DB::raw('month_date')]);

        if ($this->indexExists('monthly_funds', 'monthly_funds_month_date_unique')) {
            Schema::table('monthly_funds', function (Blueprint $table) {
                $table->dropUnique('monthly_funds_month_date_unique');
            });
        }

        Schema::table('monthly_funds', function (Blueprint $table) {
            try {
                $table->date('month_date')->nullable()->change();
            } catch (\Throwable) {
                //
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'payment_date')) {
                $table->date('payment_date')->nullable()->after('user_id')->index();
            }
        });

        DB::table('payments')
            ->whereNull('payment_date')
            ->update(['payment_date' => DB::raw('COALESCE(DATE(paid_at), month_date)')]);

        Schema::table('payments', function (Blueprint $table) {
            try {
                $table->date('month_date')->nullable()->change();
            } catch (\Throwable) {
                //
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->where('table_schema', $connection->getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();
        }

        return collect($connection->select("select indexname from pg_indexes where tablename = ?", [$table]))
            ->contains(fn ($row) => ($row->indexname ?? null) === $index);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_date')) {
                $table->dropColumn('payment_date');
            }
        });

        Schema::table('monthly_funds', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_funds', 'fund_date')) {
                $table->dropColumn('fund_date');
            }
        });
    }
};
