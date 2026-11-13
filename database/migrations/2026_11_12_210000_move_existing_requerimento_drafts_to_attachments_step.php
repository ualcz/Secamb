<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('requerimento_rascunhos')
            ->where('etapa', 2)
            ->update(['etapa' => 3]);
    }

    public function down(): void
    {
        DB::table('requerimento_rascunhos')
            ->where('etapa', 3)
            ->update(['etapa' => 2]);
    }
};
