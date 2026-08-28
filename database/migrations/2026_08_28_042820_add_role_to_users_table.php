<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // UserRole, nullable: a freshly self-registered user has no
            // role yet and is blocked from every /admin route (see
            // EnsureUserHasRole) until an existing admin assigns one.
            $table->string('role')->nullable()->after('email');
        });

        // Every user that already exists today had full, undifferentiated
        // admin access - preserve that instead of silently locking
        // everyone out the moment this migration runs.
        DB::table('users')->update(['role' => UserRole::Admin->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
