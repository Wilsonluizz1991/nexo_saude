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
            $table->string('asaas_customer_id')->nullable()->after('id');
            $table->string('asaas_subscription_id')->nullable()->after('asaas_customer_id');
            $table->string('billing_status')->default('trial')->after('asaas_subscription_id');
            $table->string('billing_payment_method')->nullable()->after('billing_status');
            $table->decimal('billing_amount', 10, 2)->nullable()->after('billing_payment_method');
            $table->timestamp('trial_ends_at')->nullable()->after('billing_amount');
            $table->timestamp('next_billing_at')->nullable()->after('trial_ends_at');
            $table->timestamp('subscription_started_at')->nullable()->after('next_billing_at');
            $table->timestamp('subscription_canceled_at')->nullable()->after('subscription_started_at');
            $table->timestamp('billing_suspended_at')->nullable()->after('subscription_canceled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_customer_id',
                'asaas_subscription_id',
                'billing_status',
                'billing_payment_method',
                'billing_amount',
                'trial_ends_at',
                'next_billing_at',
                'subscription_started_at',
                'subscription_canceled_at',
                'billing_suspended_at',
            ]);
        });
    }
};