<script setup>
import { Skeleton } from "../ui/skeleton";
</script>
<template>
    <div class="space-y-4">
        <div
            v-for="i in 5"
            :key="i"
            class="flex items-center space-x-4 p-4 border rounded-lg"
        >
            <Skeleton class="h-12 w-12 rounded-full" />
            <div class="space-y-2 flex-1">
                <Skeleton class="h-4 w-[250px]" />
                <Skeleton class="h-4 w-[200px]" />
            </div>
            <Skeleton class="h-4 w-[100px]" />
        </div>
    </div>
</template>
