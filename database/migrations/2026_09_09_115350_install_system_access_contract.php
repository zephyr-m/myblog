<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'role.view' => 'Просмотр ролей',
            'role.update' => 'Изменение ролей',
            'user.view' => 'Просмотр пользователей',
            'user.create' => 'Создание пользователей',
            'user.update' => 'Изменение пользователей',
            'user.delete' => 'Удаление пользователей',
        ];

        DB::transaction(function () use ($permissions): void {
            foreach ($permissions as $code => $name) {
                DB::table('permissions')->updateOrInsert(
                    ['code' => $code],
                    ['name' => $name, 'updated_at' => now(), 'created_at' => now()],
                );
            }

            DB::table('roles')->updateOrInsert(
                ['code' => 'super_admin'],
                ['name' => 'Суперадминистратор', 'updated_at' => now(), 'created_at' => now()],
            );

            $roleId = DB::table('roles')->where('code', 'super_admin')->value('id');
            $permissionIds = DB::table('permissions')->whereIn('code', array_keys($permissions))->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Назначения прав являются пользовательской конфигурацией и не удаляются откатом.
    }
};
