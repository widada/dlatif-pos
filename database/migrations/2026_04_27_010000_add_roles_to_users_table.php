<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add columns without unique constraint first
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 20)->nullable()->after('name');
            $table->enum('role', ['admin', 'kasir'])->default('kasir')->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->boolean('must_change_password')->default(false)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->softDeletes();

            $table->string('email')->nullable()->change();

            $table->index('role');
            $table->index('is_active');
        });

        // Step 2: Populate username from email for existing users
        DB::table('users')->orderBy('id')->each(function ($user) {
            $username = explode('@', $user->email)[0];
            DB::table('users')->where('id', $user->id)->update([
                'username' => $username,
                'role' => 'admin',
            ]);
        });

        // Step 3: Make username required and unique
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 20)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'username', 'role', 'phone', 'is_active',
                'must_change_password', 'last_login_at',
                'failed_login_attempts', 'locked_until',
            ]);
        });
    }
};
