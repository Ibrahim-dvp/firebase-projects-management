<script setup>
import { Skeleton } from "@/Components/ui/skeleton";
</script>
<template>
    <div class="space-y-4">
        <div
            v-for="i in 3"
            :key="i"
            class="flex items-center space-x-4 p-4 border rounded-lg"
        >
            <Skeleton class="h-10 w-10 rounded-full" />
            <div class="space-y-2 flex-1">
                <Skeleton class="h-4 w-[200px]" />
                <Skeleton class="h-4 w-[150px]" />
            </div>
            <Skeleton class="h-9 w-[80px]" />
            <Skeleton class="h-9 w-[80px]" />
        </div>
    </div>
</template>
