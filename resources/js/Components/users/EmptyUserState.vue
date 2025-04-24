<script setup>
import { UsersIcon, RefreshCwIcon } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
defineEmits(["refreshUsers"]);
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <UsersIcon class="h-12 w-12 text-muted-foreground mb-4" />
        <h3 class="text-lg font-medium">No users found</h3>
        <p class="text-sm text-muted-foreground mb-4">
            This project has no authentication users yet
        </p>
        <Button variant="outline" @click="$emit('refreshUsers')">
            <RefreshCwIcon class="h-4 w-4 mr-2" />
            Refresh
        </Button>
    </div>
</template>
