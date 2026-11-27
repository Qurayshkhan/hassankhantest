<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Company::class)->nullable()->constrained('companies')->cascadeOnDelete();

            $table->bigInteger('challan_number')->unique();
            $table->string('customer_name')->nullable();
            $table->bigInteger('po_number')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->longText('signature_file')->nullable();
            $table->string('signature_text')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_challans');
    }
};
