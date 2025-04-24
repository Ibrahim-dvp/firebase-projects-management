<script setup>
import { Input } from "../ui/input";
import { Button } from "../ui/button";
import { SearchIcon } from "lucide-vue-next";
defineProps({
    googleAccounts: {
        type: Array,
        default: () => [],
    },
});

defineEmits(["update:searchQuery"]);
</script>

<template>
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1">
            <Input
                :modelValue="searchQuery"
                @update:modelValue="$emit('update:searchQuery', $event)"
                placeholder="Search users by email..."
            />
            <Button
                variant="ghost"
                size="icon"
                class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8"
            >
                <SearchIcon class="h-4 w-4" />
            </Button>
        </div>
        <!-- Add more filters here later -->
    </div>
</template>
