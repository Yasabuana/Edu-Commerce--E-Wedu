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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->foreignId('delivery_point_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('shipping_method', ['pickup_point', 'custom_delivery'])
                ->default('pickup_point');
            $table->text('shipping_address')->nullable();
            $table->decimal('shipping_lat', 10, 7)->nullable();
            $table->decimal('shipping_lng', 10, 7)->nullable();
            $table->decimal('shipping_distance_km', 6, 2)->default(0);
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total');
            $table->enum('payment_method', ['cod', 'qris']);
            $table->enum('payment_status', ['unpaid', 'awaiting_verification', 'paid', 'rejected'])
                ->default('unpaid');
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'ready',
                'shipped', 'delivered', 'completed', 'cancelled',
            ])->default('pending');
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
