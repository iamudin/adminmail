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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number');
            $table->string('status')->default('pending');
            $table->integer('jumlah_tagihan')->default(0);
            $table->string('bukti_pembayaran')->nullable();
            $table->year('dari_tahun')->nullable();
            $table->year('ke_tahun')->nullable();
            $table->date('expired')->nullable();
            $table->datetime('dilunasi_pada')->nullable();
            $table->datetime('jatuh_tempo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
