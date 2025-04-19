<script setup>
defineProps({
    error: {
        type: String,
        required: true,
    },
});
import { Alert, AlertTitle, AlertDescription } from "@/Components/ui/alert";
import { Button } from "@/Components/ui/button";

const emit = defineEmits(["refreshProjects"]);
</script>

<template>
    <Alert variant="destructive">
        <AlertTitle>Error fetching projects</AlertTitle>
        <AlertDescription>
            {{ error }}
            <Button
                variant="link"
                size="sm"
                class="h-auto p-0 ml-1"
                @click="$emit('refreshProjects')"
            >
                Try again
            </Button>
        </AlertDescription>
    </Alert>
</template>
