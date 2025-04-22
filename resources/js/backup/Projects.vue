<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import { ref, onMounted, computed } from "vue";

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

// const selectedAccountEmail = ref("");
// const firebaseProjects = ref([]);
// const selectedAccountEmail = ref(props.selectedEmail || "all");
// const searchQuery = ref("");
// const isLoading = ref(false);
// const error = ref(null);

// const filteredProjects = computed(() => {
//     let projects = firebaseProjects.value;

//     if (selectedAccountEmail.value) {
//         if (selectedAccountEmail.value === "all") {
//             projects = projects.filter(
//                 (project) => project.accountEmail !== undefined
//             );
//         } else {
//             projects = projects.filter(
//                 (project) => project.accountEmail === selectedAccountEmail.value
//             );
//         }
//     }

//     if (searchQuery.value) {
//         const query = searchQuery.value.toLowerCase();
//         projects = projects.filter(
//             (project) =>
//                 (project.displayName || "").toLowerCase().includes(query) ||
//                 (project.projectId || "").toLowerCase().includes(query)
//         );
//         console.log("Filtered projects:", projects);
//     }

//     return projects;
// });

// const fetchProjects = async () => {
//     isLoading.value = true;
//     error.value = null;
//     firebaseProjects.value = [];

//     try {
//         // Loop through all Google accounts and fetch projects
//         const projectPromises = props.googleAccounts.map(async (account) => {
//             const response = await axios.get(
//                 `https://firebase.googleapis.com/v1beta1/projects`,
//                 {
//                     headers: {
//                         Authorization: `Bearer ${account.access_token}`,
//                     },
//                 }
//             );
//             // Add accountEmail to each project
//             return (response.data.results || []).map((project) => ({
//                 ...project,
//                 accountEmail: account.email,
//             }));
//         });
//         // Wait for all requests to complete
//         const allProjects = await Promise.all(projectPromises);
//         // Flatten the array of projects
//         firebaseProjects.value = allProjects.flat();
//     } catch (err) {
//         error.value =
//             err.response?.data?.error?.message || "Failed to fetch projects";
//         console.error("API Error:", err);
//     } finally {
//         isLoading.value = false;
//     }
// };

// const refreshProjects = () => {
//     fetchProjects();
// };

// onMounted(() => {
//     fetchProjects();
//     console.log(firebaseProjects);
// });
// Reactive state
const selectedAccountEmail = ref(props.selectedEmail || "all");
const searchQuery = ref("");

// Composable usage
const { firebaseProjects, isLoading, error, fetchProjects } =
    useFirebaseProjects(props.googleAccounts);

const { filteredProjects } = useProjectFilters(
    firebaseProjects,
    selectedAccountEmail,
    searchQuery
);

// Lifecycle
onMounted(() => {
    fetchProjects();
});

const refreshProjects = () => fetchProjects();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Firebase Projects" />

        <ProjectsHead
            :isLoading="isLoading"
            @refreshProjects="refreshProjects"
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
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
