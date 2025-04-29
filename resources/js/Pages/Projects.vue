<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";

import useFirebaseProjects from "@/Composables/useFirebaseProjects";
import useProjectFilters from "@/Composables/useProjectFilters";

import ProjectsHead from "@/Components/projects/ProjectsHead.vue";
import ProjectsAlert from "@/Components/projects/projectsAlert.vue";
import EmptyProjectState from "@/Components/projects/EmptyProjectState.vue";
import ProjectSkeleton from "@/Components/projects/ProjectSkeleton.vue";
import ProjectList from "@/Components/projects/ProjectList.vue";
import ProjectsFilters from "@/Components/projects/ProjectsFilters.vue";

const props = defineProps({
    googleAccounts: {
        type: Array,
        default: () => [],
    },
    selectedEmail: String,
});
// Reactive state
const selectedAccountEmail = ref(props.selectedEmail || "all");
const searchQuery = ref("");

const { firebaseProjects, isLoading, error, fetchProjects } =
    useFirebaseProjects();

const { filteredProjects } = useProjectFilters(
    firebaseProjects,
    selectedAccountEmail,
    searchQuery
);

// Fetch projects when needed
const loadProjects = async () => {
    await fetchProjects(props.googleAccounts);
};

// Lifecycle
onMounted(() => {
    loadProjects();
});

const refreshProjects = () => loadProjects();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Firebase Projects" />

        <ProjectsHead
            :isLoading="isLoading"
            @refreshProjects="refreshProjects"
            :googleAccounts="props.googleAccounts"
        />

        <div class="space-y-6">
            <!-- Search and Filter -->
            <ProjectsFilters
                :selectedAccountEmail="selectedAccountEmail"
                :searchQuery="searchQuery"
                :googleAccounts="props.googleAccounts"
                @update:searchQuery="searchQuery = $event"
                @update:selectedAccountEmail="selectedAccountEmail = $event"
            />
            <!-- Error Alert -->
            <ProjectsAlert v-if="error" :error="error" />
            <!-- Loading State -->
            <ProjectSkeleton v-if="isLoading" />

            <!-- Empty State -->
            <EmptyProjectState
                v-if="!isLoading && firebaseProjects.length === 0"
                @refreshProjects="refreshProjects"
            />
            <!-- Projects Grid -->
            <div
                v-if="!isLoading && firebaseProjects.length > 0"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <ProjectList
                    v-for="project in filteredProjects"
                    :key="project.projectId"
                    :project="project"
                    :googleAccounts="props.googleAccounts"
                    @refreshProjects="refreshProjects"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
