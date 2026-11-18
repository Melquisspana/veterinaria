<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind')->default('vaccine');
            $table->string('product');
            $table->string('batch')->nullable();
            $table->date('applied_at');
            $table->date('next_due_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('next_due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
