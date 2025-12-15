<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Campos para PagoFácil QR
            $table->string('pagofacil_transaction_id', 100)->nullable()->after('comprobante_url');
            $table->string('company_transaction_id', 100)->nullable()->after('pagofacil_transaction_id');
            $table->text('qr_base64')->nullable()->after('company_transaction_id');
            $table->string('qr_status', 20)->nullable()->after('qr_base64')->default('PENDING');
            $table->timestamp('qr_expiration')->nullable()->after('qr_status');
            
            // Actualizar CHECK constraint de metodo_pago para incluir QR
            // Nota: PostgreSQL requiere recrear la constraint
        });
        
        // Actualizar constraint de metodo_pago para incluir QR
        DB::statement("ALTER TABLE pagos DROP CONSTRAINT IF EXISTS pagos_metodo_pago_check");
        DB::statement("ALTER TABLE pagos ADD CONSTRAINT pagos_metodo_pago_check CHECK (metodo_pago IN ('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'QR'))");
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'pagofacil_transaction_id',
                'company_transaction_id',
                'qr_base64',
                'qr_status',
                'qr_expiration',
            ]);
        });
        
        // Restaurar constraint original
        DB::statement("ALTER TABLE pagos DROP CONSTRAINT IF EXISTS pagos_metodo_pago_check");
        DB::statement("ALTER TABLE pagos ADD CONSTRAINT pagos_metodo_pago_check CHECK (metodo_pago IN ('EFECTIVO', 'TARJETA', 'TRANSFERENCIA'))");
    }
};



