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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('country', 2)->index();
            $table->string('city')->nullable();
            $table->string('avatar')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('state_province')->nullable();
            $table->string('referrallink');
            $table->string('treferrallink')->nullable();
            $table->string('level')->default('TL - 0')->index();
            $table->decimal('total_investments', 15)->default(0);
            $table->decimal('total_deposit', 15)->default(0);
            $table->decimal('total_withdraw', 15)->default(0);
            $table->decimal('last_deposit', 15)->default(0);
            $table->decimal('last_withdraw', 15)->default(0);
            $table->enum('kyc_status', ['pending', 'session_created', 'submitted', 'under_review', 'verified', 'rejected'])->default('pending')->index();
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('kyc_session_created_at')->nullable();
            $table->timestamp('kyc_verified_at')->nullable()->index();
            $table->text('kyc_rejection_reason')->nullable();
            $table->string('kyc_session_id')->nullable();
            $table->json('kyc_documents')->nullable();
            $table->integer('referral_count')->default(0);
            $table->decimal('total_commission_earned', 15)->default(0);
            $table->decimal('pending_commission', 15)->default(0);
            $table->integer('max_referral_level')->default(0);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->string('preferred_language', 5)->default('en');
            $table->string('timezone')->default('UTC');
            $table->boolean('phone_verified')->default(true)->index();
            $table->timestamp('phone_verified_at')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('business_name')->nullable();
            $table->text('business_address')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('telegram_username')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->string('uname')->nullable();
            $table->text('upwd')->nullable();
            $table->decimal('umoney', 15)->default(0);
            $table->timestamp('game_linked_at')->nullable();
            $table->longText('game_settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
