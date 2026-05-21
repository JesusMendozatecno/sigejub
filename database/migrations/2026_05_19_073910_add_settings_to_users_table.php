<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('theme')->default('light')->after('role');
            $table->string('language')->default('es')->after('theme');
            $table->string('accent_color')->default('#1a365d')->after('language');
            $table->boolean('two_factor_enabled')->default(false)->after('accent_color');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->string('notification_email')->default('all')->after('two_factor_secret');
            $table->string('notification_system')->default('all')->after('notification_email');
            $table->boolean('profile_public')->default(true)->after('notification_system');
            $table->timestamp('last_login_at')->nullable()->after('profile_public');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'theme', 'language', 'accent_color',
                'two_factor_enabled', 'two_factor_secret',
                'notification_email', 'notification_system',
                'profile_public', 'last_login_at', 'last_login_ip'
            ]);
        });
    }
};
