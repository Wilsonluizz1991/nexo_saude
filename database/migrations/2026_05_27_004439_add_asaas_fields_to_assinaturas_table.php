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
        Schema::table('assinaturas', function (Blueprint $table) {

            // Gateway
            $table->string('gateway')->nullable()->after('id');

            // IDs Asaas
            $table->string('asaas_customer_id')->nullable()->after('gateway');
            $table->string('asaas_subscription_id')->nullable()->after('asaas_customer_id');

            // Billing
            $table->decimal('valor', 10, 2)->default(49.90)->after('asaas_subscription_id');
            $table->string('status')->default('trialing')->after('valor');

            // Trial / cobrança
            $table->timestamp('trial_started_at')->nullable()->after('status');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            $table->timestamp('next_payment_at')->nullable()->after('trial_ends_at');
            $table->timestamp('last_payment_at')->nullable()->after('next_payment_at');

            // Cartão
            $table->string('card_brand')->nullable()->after('last_payment_at');
            $table->string('card_last_four', 4)->nullable()->after('card_brand');

            // Controle
            $table->timestamp('canceled_at')->nullable()->after('card_last_four');
            $table->timestamp('expired_at')->nullable()->after('canceled_at');

            // Webhook / auditoria
            $table->json('gateway_payload')->nullable()->after('expired_at');

            // Índices
            $table->index('asaas_customer_id');
            $table->index('asaas_subscription_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assinaturas', function (Blueprint $table) {

            $table->dropIndex(['asaas_customer_id']);
            $table->dropIndex(['asaas_subscription_id']);
            $table->dropIndex(['status']);

            $table->dropColumn([
                'gateway',
                'asaas_customer_id',
                'asaas_subscription_id',
                'valor',
                'status',
                'trial_started_at',
                'trial_ends_at',
                'next_payment_at',
                'last_payment_at',
                'card_brand',
                'card_last_four',
                'canceled_at',
                'expired_at',
                'gateway_payload',
            ]);
        });
    }
};