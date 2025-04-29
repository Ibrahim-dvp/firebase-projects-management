<script setup>
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

import { computed } from "vue";

const props = defineProps({
    googleAccounts: {
        type: Array,
        default: () => [],
    },
    selectedAccountEmail: String,
    searchQuery: String,
});

// Computed property for searchQuery with getter/setter
const searchQueryModel = computed({
    get: () => props.searchQuery,
    set: (value) => emit("update:searchQuery", value),
});

// Computed property for selectedAccountEmail with getter/setter
const selectedAccountEmailModel = computed({
    get: () => props.selectedAccountEmail,
    set: (value) => emit("update:selectedAccountEmail", value),
});

const emit = defineEmits(["update:searchQuery", "update:selectedAccountEmail"]);
</script>
<template>
    <div class="flex flex-col sm:flex-row gap-4">
        <Input
            v-model="searchQueryModel"
            type="text"
            placeholder="Search projects..."
            class="flex-1 p-5"
        />

        <!-- <Select v-model="selectedAccountEmailModel"> -->
        <Select
            :modelValue="selectedProject"
            @update:modelValue="handleProjectChange"
        >
            <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Filter by account" />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    <SelectLabel>Google Accounts</SelectLabel>
                    <SelectItem value="all"> All Accounts </SelectItem>
                    <SelectItem
                        v-for="account in props.googleAccounts"
                        :key="account.id"
                        :value="account.email"
                    >
                        {{ account.email }}
                    </SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>
    </div>
</template>
