<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    KeyRound,
    ShieldCheck,
    UserPlus,
    Users,
    UserRoundX,
    type LucideIcon,
} from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { edit as roles } from '@/routes/system/roles';
import { create as createUser, index as users } from '@/routes/system/users';
import { usePermissions } from '@/system/access/usePermissions';
import type { Role, User } from '@/types';

type DashboardStat = {
    code: 'users' | 'roles' | 'permissions' | 'users_without_roles';
    label: string;
    value: number;
    description: string;
};

type RecentUser = Pick<User, 'id' | 'name' | 'email' | 'created_at'> & {
    roles: Role[];
};

defineProps<{
    stats: DashboardStat[];
    recentUsers: RecentUser[];
    accessRoles: Role[];
}>();

const page = usePage();
const { can } = usePermissions();
const statIcons: Record<DashboardStat['code'], LucideIcon> = {
    users: Users,
    roles: ShieldCheck,
    permissions: KeyRound,
    users_without_roles: UserRoundX,
};

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('ru-RU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Обзор',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Обзор системы" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
        <Heading
            title="Обзор системы"
            description="Текущее состояние пользователей и управления доступом"
        />

        <section
            v-if="stats.length"
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            <Card v-for="stat in stats" :key="stat.code">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm font-medium">
                        {{ stat.label }}
                    </CardTitle>
                    <component
                        :is="statIcons[stat.code]"
                        class="text-muted-foreground size-4"
                    />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-semibold tracking-tight">
                        {{ stat.value }}
                    </p>
                    <p class="text-muted-foreground mt-1 text-xs">
                        {{ stat.description }}
                    </p>
                </CardContent>
            </Card>
        </section>

        <div
            class="grid flex-1 gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(20rem,1fr)]"
        >
            <Card v-if="can('user.view')">
                <CardHeader
                    class="flex flex-row items-start justify-between gap-4"
                >
                    <div class="space-y-1.5">
                        <CardTitle>Новые пользователи</CardTitle>
                        <CardDescription>
                            Последние пять учётных записей
                        </CardDescription>
                    </div>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="users()">
                            Все пользователи
                            <ArrowRight />
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="recentUsers.length" class="divide-y">
                        <div
                            v-for="user in recentUsers"
                            :key="user.id"
                            class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ user.name }}
                                </p>
                                <p
                                    class="text-muted-foreground truncate text-sm"
                                >
                                    {{ user.email }}
                                </p>
                            </div>
                            <div
                                class="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end"
                            >
                                <Badge
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    variant="secondary"
                                >
                                    {{ role.name }}
                                </Badge>
                                <Badge
                                    v-if="user.roles.length === 0"
                                    variant="outline"
                                >
                                    Без роли
                                </Badge>
                                <span class="text-muted-foreground text-xs">
                                    {{ formatDate(user.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p
                        v-else
                        class="text-muted-foreground py-8 text-center text-sm"
                    >
                        Пользователей пока нет.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Ваш доступ</CardTitle>
                    <CardDescription>
                        Роли и права текущей учётной записи
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-5">
                    <div class="space-y-2">
                        <p class="text-sm font-medium">Роли</p>
                        <div class="flex flex-wrap gap-2">
                            <Badge
                                v-for="role in accessRoles"
                                :key="role.id"
                                variant="secondary"
                            >
                                {{ role.name }}
                            </Badge>
                            <span
                                v-if="accessRoles.length === 0"
                                class="text-muted-foreground text-sm"
                            >
                                Роли не назначены
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm font-medium">Права</p>
                        <div class="flex flex-wrap gap-2">
                            <Badge
                                v-if="page.props.auth.permissions.includes('*')"
                            >
                                Полный доступ
                            </Badge>
                            <Badge
                                v-for="permission in page.props.auth.permissions.filter(
                                    (permission) => permission !== '*',
                                )"
                                :key="permission"
                                variant="outline"
                            >
                                {{ permission }}
                            </Badge>
                            <span
                                v-if="page.props.auth.permissions.length === 0"
                                class="text-muted-foreground text-sm"
                            >
                                Права не назначены
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card
            v-if="can('user.create') || can('role.view')"
            class="xl:max-w-2xl"
        >
            <CardHeader>
                <CardTitle>Быстрые действия</CardTitle>
                <CardDescription>
                    Основные операции системного раздела
                </CardDescription>
            </CardHeader>
            <CardContent class="flex flex-wrap gap-3">
                <Button v-if="can('user.create')" as-child>
                    <Link :href="createUser()">
                        <UserPlus />
                        Добавить пользователя
                    </Link>
                </Button>
                <Button v-if="can('role.view')" variant="outline" as-child>
                    <Link :href="roles()">
                        <ShieldCheck />
                        Матрица ролей
                    </Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
