<?php

use App\Models\Quotation;
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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Quotation::class)->nullable()->constrained('quotations')->cascadeOnDelete();
            $table->bigInteger('pr')->nullable();
            $table->longText('description')->nullable();
            $table->string('unit')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->double('rate_per_unit')->default(0);
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
        Schema::dropIfExists('quotation_items');
    }
};
