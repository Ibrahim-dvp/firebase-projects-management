<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { Input } from "../ui/input";
import { Button } from "../ui/button";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectGroup,
    SelectTrigger,
    SelectLabel,
    SelectValue,
} from "../ui/select";
import { SearchIcon, RefreshCwIcon } from "lucide-vue-next";

const props = defineProps({
    firebaseProjects: {
        type: Array,
        default: () => [],
    },
    googleAccounts: Array,
    selectedProject: String,
    searchQuery: String,
    isLoading: Boolean,
});

const emit = defineEmits([
    "update:searchQuery",
    "update:isLoading",
    "refreshUsers",
]);

// Track selected account email
const selectedAccountEmail = ref(null);

// Set initial account email based on selected project
if (props.selectedProject) {
    const project = props.firebaseProjects.find(
        (p) => p.project_id === props.selectedProject
    );
    selectedAccountEmail.value = project?.email || null;
}

// Filter projects by selected account email
const filteredProjects = computed(() => {
    if (!selectedAccountEmail.value) return props.firebaseProjects;
    return props.firebaseProjects.filter(
        (project) => project.email === selectedAccountEmail.value
    );
});

// When account changes, update the projects list
const handleAccountChange = (email) => {
    selectedAccountEmail.value = email;
    // Optional: Reset project selection when account changes
    emit("update:isLoading", true);
    router.get(
        "/users",
        {
            account: email,
            project: null, // Reset project filter
        },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                emit("update:isLoading", false);
            },
        }
    );
};

const handleProjectChange = (projectId) => {
    emit("update:isLoading", true);
    router.get(
        "/users",
        {
            account: selectedAccountEmail.value, // Keep account filter
            project: projectId,
        },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                emit("update:isLoading", false);
            },
        }
    );
};
</script>

<template>
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1">
            <Input
                class="p-5"
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

        <!-- Google Account Selector -->
        <div class="relative flex-2">
            <Select
                :modelValue="selectedAccountEmail"
                @update:modelValue="handleAccountChange"
            >
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Select account" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Google Accounts</SelectLabel>
                        <SelectItem
                            v-for="account in googleAccounts"
                            :key="account.id"
                            :value="account.email"
                        >
                            {{ account.email }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <!-- Project Selector (shows filtered projects) -->
        <div class="relative flex-2">
            <Select
                :modelValue="selectedProject"
                @update:modelValue="handleProjectChange"
                :disabled="!selectedAccountEmail"
            >
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Select project" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Firebase Projects</SelectLabel>
                        <SelectItem
                            v-for="project in filteredProjects"
                            :key="project.id"
                            :value="project.project_id"
                        >
                            {{ project.name }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <Button
            class="p-5"
            variant="outline"
            :disabled="isLoading"
            @click="$emit('refreshUsers')"
        >
            <RefreshCwIcon
                class="h-4 w-4 mr-2"
                :class="{ 'animate-spin': isLoading }"
            />
            Refresh
        </Button>
    </div>
</template>
