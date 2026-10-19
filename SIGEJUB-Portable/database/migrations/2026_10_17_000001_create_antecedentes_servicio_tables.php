<?php
// Migración para crear las tablas de antecedentes de servicio y sus documentos
// de respaldo. Cada antecedente representa un período laboral del trabajador
// (dentro o fuera de la institución) con cargo, condición y dedicación; cada
// documento registra la providencia, folio y archivo digital que lo acredita.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antecedentes_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->cascadeOnDelete();
            $table->string('institucion', 150);                  // Institución donde prestó servicio
            $table->boolean('es_institucion_actual')->default(false); // true = presta servicio aquí
            $table->string('cargo', 150)->nullable();            // Cargo desempeñado en el período
            $table->string('condicion_laboral', 100)->nullable(); // Condición en ese período
            $table->string('dedicacion', 100)->nullable();       // Dedicación en ese período
            $table->date('fecha_inicio');                        // Fecha de vigencia del período
            $table->date('fecha_fin')->nullable();               // null = período vigente
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('antecedente_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('antecedente_id')->constrained('antecedentes_servicio')->cascadeOnDelete();
            $table->string('tipo_documento', 100);               // Constancia, Oficio, Providencia, etc.
            $table->string('numero_providencia', 50)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->string('institucion_emisora', 150)->nullable();
            $table->string('folio_numero', 50)->nullable();      // N° de hoja/folio de archivo
            $table->unsignedSmallInteger('folio_hojas')->nullable(); // N° de hojas del documento
            $table->text('observaciones')->nullable();
            $table->string('archivo')->nullable();               // PDF o imagen (storage público)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antecedente_documentos');
        Schema::dropIfExists('antecedentes_servicio');
    }
};
