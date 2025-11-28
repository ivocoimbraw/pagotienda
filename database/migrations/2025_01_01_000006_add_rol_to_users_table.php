<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['admin', 'vendedor', 'cliente'])->default('cliente')->after('email');
            $table->string('telefono')->nullable()->after('rol');
            $table->string('direccion')->nullable()->after('telefono');
            $table->boolean('activo')->default(true)->after('direccion');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'telefono', 'direccion', 'activo']);
        });
    }
};
