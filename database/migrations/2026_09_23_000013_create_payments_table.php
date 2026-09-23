<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * COD & QRIS dalam satu tabel; `gateway_response` disiapkan untuk
     * migrasi ke payment gateway tanpa ubah struktur (Rancangan §1.2).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['cod', 'qris']);
            $table->unsignedBigInteger('amount');
            $table->string('proof_path')->nullable();       // disk private
            $table->string('sender_name', 150)->nullable();
            $table->string('sender_bank', 100)->nullable();
            $table->string('qris_reference', 100)->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
