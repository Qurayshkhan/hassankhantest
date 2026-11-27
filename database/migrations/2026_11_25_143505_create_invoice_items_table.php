<?php

use App\Models\Invoice;
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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Invoice::class)->nullable()->constrained('invoices')->cascadeOnDelete();
            $table->longText('description')->nullable();
            $table->string('unit')->nullable();
            $table->double('quantity')->default(0);
            $table->double('price_per_unit')->default(0);
            $table->double('amount_excluding_sale_tax')->default(0);
            $table->double('plus_gs_t18')->default(0);
            $table->double('line_total')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
