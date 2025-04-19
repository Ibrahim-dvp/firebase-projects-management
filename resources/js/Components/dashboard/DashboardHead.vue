<script setup>
import { RefreshCcwIcon } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";

import { defineProps } from "vue";

const props = defineProps({
    isLoading: {
        type: Boolean,
        default: false,
    },
});
</script>
<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">
                    Google Accounts
                </h2>
                <p class="text-sm text-muted-foreground mt-1">
                    Manage your Google Accounts and their associated projects.
                </p>
            </div>
            <Button
                variant="outline"
                :disabled="props.isLoading"
                @click="$emit('linkGoogleAccount')"
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
