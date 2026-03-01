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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('banner')->nullable();
            $table->string('leader')->nullable();
            $table->string('coordinator')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('street', 100)->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('url')->nullable();
            $table->string('gpwhatsapp')->nullable();
            $table->decimal('price', 8, 2)->default('0.00')->nullable(); // Preço da inscrição
            $table->decimal('price_church', 8, 2)->default('0.00')->nullable(); // valor destinado a igreja para pagamento das despesas
            $table->decimal('discount', 8, 2)->default('0.00')->nullable(); // possibilidade de porcentagem para desconto
            $table->integer('kits')->default(0)->nullable(); // informa se a pessoa já recebeu manterial
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(0)->nullable();
            $table->unsignedSmallInteger(column: 'welcome_duration_minutes')->default(30);
            $table->json('schedule_settings')->nullable();
            $table->timestamps();

            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->cascadeOnUpdate()->onDelete('set null');
            $table->foreignId('church_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
