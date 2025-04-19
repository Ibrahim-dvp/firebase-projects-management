<script setup>
import { Skeleton } from "@/Components/ui/skeleton";
import {
    Card,
    CardHeader,
    CardContent,
    CardFooter,
} from "@/Components/ui/card";
</script>
<template>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card v-for="i in 3" :key="i">
            <CardHeader class="space-y-2">
                <Skeleton class="h-5 w-3/4" />
                <Skeleton class="h-4 w-1/2" />
            </CardHeader>
            <CardContent class="space-y-3">
                <div class="space-y-2">
                    <Skeleton class="h-4 w-1/3" />
                    <Skeleton class="h-4 w-full" />
                </div>
                <div class="space-y-2">
                    <Skeleton class="h-4 w-1/3" />
                    <div class="flex gap-2">
                        <Skeleton class="h-5 w-16 rounded-full" />
                        <Skeleton class="h-5 w-16 rounded-full" />
                    </div>
                </div>
            </CardContent>
            <CardFooter>
                <Skeleton class="h-4 w-24" />
            </CardFooter>
        </Card>
    </div>
</template>
