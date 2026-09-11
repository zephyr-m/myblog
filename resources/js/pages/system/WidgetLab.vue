<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { widgets } from '@/routes/system';
import MetricWidget from '@/system/widgets/MetricWidget.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Лаборатория виджетов',
                href: widgets(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Лаборатория виджетов" />

    <div class="space-y-6 p-4">
        <Heading
            title="Лаборатория виджетов"
            description="Изолированная площадка для разработки и проверки интерфейсных блоков"
        />

        <section class="space-y-3">
            <div>
                <h2 class="text-lg font-semibold">Метрики</h2>
                <p class="text-muted-foreground text-sm">
                    Компактные числовые показатели.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <MetricWidget
                    title="Пользователи"
                    :value="1"
                    description="Демонстрационное значение"
                />
            </div>
        </section>
    </div>
</template>
