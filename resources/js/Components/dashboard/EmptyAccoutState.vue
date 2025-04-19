<script setup>
import { UserIcon } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";

const emit = defineEmits(["linkGoogleAccount"]);
</script>
<template>
    <div class="flex flex-col items-center justify-center py-12 space-y-4">
        <div
            class="w-24 h-24 rounded-full bg-muted flex items-center justify-center"
        >
            <UserIcon class="w-12 h-12 text-muted-foreground" />
        </div>
        <h3 class="text-lg font-medium">No Google accounts</h3>
        <p class="text-sm text-muted-foreground text-center">
            You haven't connected any Google accounts yet
        </p>
        <Button @click="$emit('linkGoogleAccount')" class="mt-2">
            Connect Account
        </Button>
    </div>
</template>
