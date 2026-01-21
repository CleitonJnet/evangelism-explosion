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
            $table->string('coordinator')->nullable();
            $table->string('phone',20)->nullable();
            $table->string('email',100)->nullable();
            $table->string('street',100)->nullable();
            $table->string('number',20)->nullable();
            $table->string('complement',100)->nullable();
            $table->string('district',100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code',10)->nullable();
            $table->string('url')->nullable();
            $table->string('gpwhatsapp')->nullable();
            $table->string('price')->default('0,00')->nullable(); // Preço da inscrição
            $table->string('price_church')->default('0,00')->nullable(); // valor destinado a igreja para pagamento das despesas
            $table->string('discount')->default('0,00')->nullable(); // possibilidade de porcentagem para desconto
            $table->integer('kits')->default(0)->nullable(); // informa se a pessoa já recebeu manterial
            $table->integer('totStudents')->default(0);
            $table->integer('totChurches')->default(0);
            $table->integer('totNewChurches')->default(0);
            $table->integer('totPastors')->default(0);
            $table->integer('totKitsUsed')->default(0);
            $table->integer('totListeners')->default(0);
            $table->integer('totKitsReceived')->default(0);
            $table->integer('totApproaches')->default(0);
            $table->integer('totDecisions')->default(0);
            $table->text('notes')->nullable();
            $table->integer('status')->nullable();
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
