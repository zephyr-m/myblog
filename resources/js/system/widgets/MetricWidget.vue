<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    title: string;
    value: string | number;
    description?: string;
}>();
</script>

<template>
    <Card>
        <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">
                {{ title }}
            </CardTitle>
        </CardHeader>
        <CardContent>
            <p class="text-3xl font-semibold tracking-tight">
                {{ value }}
            </p>
            <p v-if="description" class="text-muted-foreground mt-1 text-xs">
                {{ description }}
            </p>
        </CardContent>
    </Card>
</template>
