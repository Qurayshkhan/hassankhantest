<?php

use App\Models\Company;
use App\Models\User;
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
        Schema::create('ses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Company::class)->nullable()->constrained('companies')->cascadeOnDelete();
            $table->bigInteger('document_number')->unique()->default(0);
            $table->date('date')->nullable();
            $table->bigInteger('po_number')->nullable();
            $table->string('rgp')->nullable();
            $table->string('igp')->nullable();
            $table->string('signature_text')->nullable();
            $table->longText('signature_file')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ses');
    }
};
