<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['administrador','cajero','cliente'])->default('cliente')->change();
        });
    }
    public function down(): void {
        // No revertimos el enum automáticamente: podrían existir cuentas cliente.
        // Para deshacer esta migración es necesario convertir primero esos usuarios.
    }
};
