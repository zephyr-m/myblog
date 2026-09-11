<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    create,
    destroy as destroyUser,
    edit,
    index,
} from '@/routes/system/users';
import { usePermissions } from '@/system/access/usePermissions';
import type { Role, User } from '@/types';

type Page<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

type ListedUser = User & { roles: Role[] };

const props = defineProps<{
    users: Page<ListedUser>;
    filters: { search: string; sort: string; direction: 'asc' | 'desc' };
}>();

const page = usePage();
const { can } = usePermissions();
const search = ref(props.filters.search);

function visit(parameters: Record<string, string | number | undefined>): void {
    router.get(
        index().url,
        {
            search: search.value || undefined,
            sort: props.filters.sort,
            direction: props.filters.direction,
            ...parameters,
        },
        { preserveScroll: true, preserveState: true, replace: true },
    );
}

function sort(column: string): void {
    visit({
        sort: column,
        direction:
            props.filters.sort === column && props.filters.direction === 'asc'
                ? 'desc'
                : 'asc',
        page: 1,
    });
}

function remove(user: ListedUser): void {
    if (confirm(`Удалить пользователя ${user.name}?`)) {
        router.delete(destroyUser(user.id).url);
    }
}
</script>

<template>
    <Head title="Пользователи" />

    <div class="space-y-6 p-4">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                title="Пользователи"
                description="Управление учётными записями и ролями"
            />
            <Button v-if="can('user.create')" as-child>
                <Link :href="create()"><Plus />Добавить</Link>
            </Button>
        </div>

        <section class="bg-card overflow-hidden rounded-xl border">
            <form
                class="flex gap-2 border-b p-4"
                @submit.prevent="visit({ page: 1 })"
            >
                <div class="relative w-full max-w-sm">
                    <Search
                        class="text-muted-foreground absolute top-2.5 left-3 size-4"
                    />
                    <Input
                        v-model="search"
                        class="pl-9"
                        placeholder="Имя или email"
                    />
                </div>
                <Button variant="outline">Найти</Button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-muted/40 text-muted-foreground text-xs uppercase"
                    >
                        <tr>
                            <th class="px-5 py-3">
                                <button type="button" @click="sort('name')">
                                    Имя
                                </button>
                            </th>
                            <th class="px-5 py-3">
                                <button type="button" @click="sort('email')">
                                    Email
                                </button>
                            </th>
                            <th class="px-5 py-3">Роли</th>
                            <th class="px-5 py-3 text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="border-t"
                        >
                            <td class="px-5 py-4 font-medium">
                                {{ user.name }}
                            </td>
                            <td class="px-5 py-4">{{ user.email }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        variant="secondary"
                                    >
                                        {{ role.name }}
                                    </Badge>
                                    <span
                                        v-if="user.roles.length === 0"
                                        class="text-muted-foreground"
                                        >Без роли</span
                                    >
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        v-if="can('user.update')"
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="edit(user.id)"
                                            ><Pencil />Изменить</Link
                                        >
                                    </Button>
                                    <Button
                                        v-if="
                                            can('user.delete') &&
                                            page.props.auth.user.id !== user.id
                                        "
                                        variant="destructive"
                                        size="sm"
                                        @click="remove(user)"
                                    >
                                        <Trash2 />Удалить
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p
                    v-if="users.data.length === 0"
                    class="text-muted-foreground p-10 text-center"
                >
                    Пользователи не найдены.
                </p>
            </div>

            <footer
                class="flex items-center justify-between border-t px-5 py-3 text-sm"
            >
                <span class="text-muted-foreground">
                    {{
                        users.total
                            ? `${users.from}–${users.to} из ${users.total}`
                            : 'Нет записей'
                    }}
                </span>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!users.prev_page_url"
                        @click="
                            users.prev_page_url &&
                            router.get(users.prev_page_url)
                        "
                    >
                        Назад
                    </Button>
                    <span
                        >{{ users.current_page }} из {{ users.last_page }}</span
                    >
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!users.next_page_url"
                        @click="
                            users.next_page_url &&
                            router.get(users.next_page_url)
                        "
                    >
                        Далее
                    </Button>
                </div>
            </footer>
        </section>
    </div>
</template>
