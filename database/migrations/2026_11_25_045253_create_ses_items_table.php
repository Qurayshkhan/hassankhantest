<?php

use App\Models\Ses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ses_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Ses::class)->nullable()->constrained('ses')->cascadeOnDelete();
            $table->longText('description')->nullable();
            $table->string('unit')->nullable();
            $table->bigInteger('quantity')->default(0);
            $table->bigInteger('rate')->default(0);
            $table->double('amount')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ses_items');
    }
};
