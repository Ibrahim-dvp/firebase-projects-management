<script setup>
import { onMounted } from "vue";
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
import { SearchIcon } from "lucide-vue-next";

const props = defineProps({
    firebaseProjects: {
        type: Array,
        default: () => [],
    },
    selectedProject: String,
    searchQuery: String,
    isLoading: Boolean,
});

const handleProjectChange = (projectId) => {
    emit("update:isLoading", true);
    router.get(
        "/users",
        {
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
const emit = defineEmits(["update:searchQuery, 'update:isLoading"]);
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
        <div class="relative flex-2">
            <Select
                :modelValue="selectedProject"
                @update:modelValue="handleProjectChange"
            >
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Filter by account" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Firebase Projects</SelectLabel>
                        <SelectItem
                            v-for="project in firebaseProjects"
                            :key="project.id"
                            :value="project.project_id"
                        >
                            {{ project.name }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>
