<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { update } from '@/routes/system/roles';

type Role = {
    id: number;
    name: string;
    code: string;
    users_count: number;
    permission_ids: number[];
    locked: boolean;
};

type Permission = { id: number; name: string; code: string };

const props = defineProps<{ roles: Role[]; permissions: Permission[] }>();
const form = useForm<{ roles: Record<string, number[]> }>({
    roles: Object.fromEntries(
        props.roles
            .filter((role) => !role.locked)
            .map((role) => [String(role.id), role.permission_ids]),
    ),
});

const groups = computed(() =>
    props.permissions.reduce<Record<string, Permission[]>>(
        (result, permission) => {
            const group = permission.code.split('.')[0];
            (result[group] ??= []).push(permission);
            return result;
        },
        {},
    ),
);

function toggle(role: Role, permission: Permission): void {
    if (role.locked || permission.code === 'role.update') return;

    const key = String(role.id);
    const current = form.roles[key] ?? [];
    form.roles[key] = current.includes(permission.id)
        ? current.filter((id) => id !== permission.id)
        : [...current, permission.id];
}
</script>

<template>
    <Head title="Роли" />

    <div class="space-y-6 p-4">
        <Heading
            title="Роли и права"
            description="Матрица системных разрешений"
        />

        <form class="space-y-5" @submit.prevent="form.put(update().url)">
            <div class="bg-card overflow-x-auto rounded-xl border">
                <table class="w-full min-w-2xl text-sm">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-4 py-3 text-left">Разрешение</th>
                            <th
                                v-for="role in roles"
                                :key="role.id"
                                class="min-w-40 px-4 py-3 text-center"
                            >
                                <span>{{ role.name }}</span>
                                <Badge
                                    v-if="role.locked"
                                    class="ml-2"
                                    variant="secondary"
                                    >Системная</Badge
                                >
                                <span
                                    class="text-muted-foreground mt-1 block text-xs font-normal"
                                >
                                    {{ role.users_count }} пользователей
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template
                            v-for="(permissions, group) in groups"
                            :key="group"
                        >
                            <tr class="bg-muted/20 border-t">
                                <td
                                    :colspan="roles.length + 1"
                                    class="px-4 py-2 font-medium uppercase"
                                >
                                    {{ group }}
                                </td>
                            </tr>
                            <tr
                                v-for="permission in permissions"
                                :key="permission.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{
                                        permission.name
                                    }}</span>
                                    <span
                                        class="text-muted-foreground block text-xs"
                                        >{{ permission.code }}</span
                                    >
                                </td>
                                <td
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="px-4 py-3 text-center"
                                >
                                    <Checkbox
                                        :model-value="
                                            role.locked ||
                                            (
                                                form.roles[String(role.id)] ??
                                                []
                                            ).includes(permission.id)
                                        "
                                        :disabled="
                                            role.locked ||
                                            permission.code === 'role.update'
                                        "
                                        @update:model-value="
                                            toggle(role, permission)
                                        "
                                    />
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <InputError :message="form.errors.roles" />
            <Button :disabled="form.processing || !form.isDirty"
                >Сохранить матрицу</Button
            >
        </form>
    </div>
</template>
