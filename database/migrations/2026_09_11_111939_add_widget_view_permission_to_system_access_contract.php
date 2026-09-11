<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('permissions')->updateOrInsert(
                ['code' => 'widget.view'],
                ['name' => 'Просмотр виджетов', 'updated_at' => now(), 'created_at' => now()],
            );

            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => DB::table('permissions')->where('code', 'widget.view')->value('id'),
                'role_id' => DB::table('roles')->where('code', 'super_admin')->value('id'),
            ]);
        });
    }

    public function down(): void
    {
        // Назначения прав являются пользовательской конфигурацией и не удаляются откатом.
    }
};
