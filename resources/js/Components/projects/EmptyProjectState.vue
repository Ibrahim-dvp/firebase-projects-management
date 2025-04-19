<script setup>
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { CuboidIcon, RefreshCcwIcon } from "lucide-vue-next";

const emit = defineEmits(["refreshProjects"]);
</script>
<template>
    <Card class="text-center">
        <CardContent class="pt-6 pb-8">
            <div
                class="mx-auto w-24 h-24 rounded-full bg-muted flex items-center justify-center mb-4"
            >
                <CuboidIcon class="w-10 h-10 text-muted-foreground" />
            </div>
            <h3 class="text-lg font-medium mb-2">No Firebase projects</h3>
            <p class="text-sm text-muted-foreground mb-4">
                You don't have any Firebase projects in this account yet.
            </p>
            <Button @click="$emit('refreshProjects')">
                <RefreshCcwIcon class="w-4 h-4 mr-2" />
                Refresh projects
            </Button>
        </CardContent>
    </Card>
</template>
