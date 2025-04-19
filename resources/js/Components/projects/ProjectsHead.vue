<script setup>
import { Button } from "@/Components/ui/button";
import { RefreshCcwIcon } from "lucide-vue-next";

defineProps({
    isLoading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["refreshProjects"]);
</script>
<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">
                    Firebase Projects
                </h2>
                <p class="text-sm text-muted-foreground mt-1">
                    Manage your Firebase projects across all connected accounts
                </p>
            </div>
            <Button
                variant="outline"
                @click="$emit('refreshProjects')"
                :disabled="isLoading"
            >
                <RefreshCcwIcon
                    class="w-4 h-4 mr-2"
                    :class="{ 'animate-spin': isLoading }"
                />
                Refresh
            </Button>
        </div>
    </div>
</template>
