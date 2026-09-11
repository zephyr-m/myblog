<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, store, update as updateUser } from '@/routes/system/users';
import type { Role, User } from '@/types';

type EditableUser = User & { roles: Role[] };

const props = defineProps<{
    user?: EditableUser;
    roles: Role[];
}>();

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles.map((role) => role.id) ?? [],
});

function toggleRole(roleId: number): void {
    form.roles = form.roles.includes(roleId)
        ? form.roles.filter((id) => id !== roleId)
        : [...form.roles, roleId];
}

function submit(): void {
    if (props.user) {
        form.put(updateUser(props.user.id).url);
        return;
    }

    form.post(store().url);
}
</script>

<template>
    <Head :title="user ? 'Изменить пользователя' : 'Новый пользователь'" />

    <div class="space-y-6 p-4">
        <Heading
            :title="user ? 'Изменить пользователя' : 'Новый пользователь'"
            description="Учётные данные и системные роли"
        />

        <form
            class="bg-card space-y-6 rounded-xl border p-6"
            @submit.prevent="submit"
        >
            <div class="grid gap-5 md:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="name">Имя</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        autocomplete="name"
                        required
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        required
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{
                        user ? 'Новый пароль' : 'Пароль'
                    }}</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        :required="!user"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation"
                        >Подтверждение пароля</Label
                    >
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        :required="!user"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>
            </div>

            <fieldset class="space-y-3">
                <legend class="font-medium">Роли</legend>
                <div v-if="roles.length" class="grid gap-3 sm:grid-cols-2">
                    <Label
                        v-for="role in roles"
                        :key="role.id"
                        class="flex items-center gap-3 rounded-lg border p-3"
                    >
                        <Checkbox
                            :model-value="form.roles.includes(role.id)"
                            @update:model-value="toggleRole(role.id)"
                        />
                        <span>
                            {{ role.name }}
                            <span class="text-muted-foreground block text-xs">{{
                                role.code
                            }}</span>
                        </span>
                    </Label>
                </div>
                <p v-else class="text-muted-foreground text-sm">
                    Доступных ролей пока нет.
                </p>
                <InputError :message="form.errors.roles" />
            </fieldset>

            <div class="flex justify-end gap-2 border-t pt-5">
                <Button variant="outline" as-child
                    ><Link :href="index()">Отмена</Link></Button
                >
                <Button :disabled="form.processing">Сохранить</Button>
            </div>
        </form>
    </div>
</template>
