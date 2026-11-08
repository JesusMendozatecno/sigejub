<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("solicitudes", function (Blueprint $table) {
            $table->string("trabajador_nombres")->nullable()->after("trabajador_id");
            $table->string("trabajador_apellidos")->nullable()->after("trabajador_nombres");
            $table->string("trabajador_cedula")->nullable()->after("trabajador_apellidos");
        });

        $solicitudes = DB::table("solicitudes")->get();
        foreach ($solicitudes as $sol) {
            if ($sol->trabajador_id) {
                $t = DB::table("trabajadores")->where("id", $sol->trabajador_id)->first();
                if ($t) {
                    DB::table("solicitudes")->where("id", $sol->id)->update([
                        "trabajador_nombres" => $t->nombres,
                        "trabajador_apellidos" => $t->apellidos,
                        "trabajador_cedula" => $t->cedula,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table("solicitudes", function (Blueprint $table) {
            $table->dropColumn(["trabajador_nombres", "trabajador_apellidos", "trabajador_cedula"]);
        });
    }
};
