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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('username')->index();
            $table->string('phone')->nullable()->index();
            $table->string('referral_code')->index();
            $table->unsignedBigInteger('sponsor_id')->nullable()->index();
            $table->enum('status', ['pending_verification', 'active', 'inactive', 'blocked'])->default('pending_verification')->index();
            $table->boolean('excluded_from_stats')->default(false);
            $table->enum('role', ['admin', 'support', 'moderator', 'user'])->default('user');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->timestamp('blocked_at')->nullable()->index('idx_users_blocked_at');
            $table->unsignedBigInteger('blocked_by')->nullable();
            $table->string('block_reason')->nullable()->index('idx_users_block_reason');
            $table->text('block_notes')->nullable();
            $table->timestamp('block_expires_at')->nullable()->index('idx_users_block_expires_at');
            $table->timestamp('unblocked_at')->nullable();
            $table->unsignedBigInteger('unblocked_by')->nullable();
            $table->string('unblock_reason')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->integer('user_level')->default(0);
            $table->decimal('total_invested', 15)->default(0);
            $table->decimal('total_earned', 15)->default(0);
            $table->timestamp('level_updated_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('password_changed_at')->nullable();
            $table->text('google2fa_secret')->nullable();
            $table->boolean('google2fa_enabled')->default(false);
            $table->timestamp('google2fa_enabled_at')->nullable();
            $table->boolean('push_notifications_enabled')->default(false);
            $table->timestamp('last_push_subscription_at')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->timestamp('bot_activated_at')->nullable();
            $table->boolean('withdraw_disabled')->default(false);
            $table->boolean('roi_disabled')->default(false);
            $table->boolean('commission_disabled')->default(false);
            $table->boolean('referral_disabled')->default(false);
            $table->unsignedBigInteger('admin_role_id')->nullable()->index('users_admin_role_id_foreign');

            $table->index(['status', 'blocked_at'], 'idx_users_status_blocked_at');
            $table->unique(['phone']);
            $table->unique(['referral_code']);
            $table->unique(['username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
