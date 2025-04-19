<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ProjectsHead from "@/Components/projects/ProjectsHead.vue";
import ProjectsAlert from "@/Components/projects/projectsAlert.vue";
import EmptyProjectState from "@/Components/projects/EmptyProjectState.vue";
import ProjectSkeleton from "@/Components/projects/ProjectSkeleton.vue";
import ProjectList from "@/Components/projects/ProjectList.vue";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";

import { ref, onMounted } from "vue";

const props = defineProps({
    token: String,
});

const firebaseProjects = ref([]);
const isLoading = ref(false);
const error = ref(null);

const fetchProjects = async () => {
    if (!props.token) return;

    isLoading.value = true;
    error.value = null;
    firebaseProjects.value = [];

    try {
        const response = await axios.get(
            `https://firebase.googleapis.com/v1beta1/projects`,
            {
                headers: {
                    Authorization: `Bearer ${props.token}`,
                },
            }
        );

        firebaseProjects.value = response.data.results || [];
    } catch (err) {
        error.value =
            err.response?.data?.error?.message || "Failed to fetch projects";
        // If token is invalid, force a page reload to trigger token refresh
        if (err.response?.status === 401) {
            router.reload();
        }
        console.error("API Error:", err);
    } finally {
        isLoading.value = false;
    }
};

const refreshProjects = () => {
    console.log("Refreshing projects...");

    fetchProjects();
};

onMounted(() => {
    fetchProjects();
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Firebase Projects" />

        <ProjectsHead
            :isLoading="isLoading"
            @refreshProjects="refreshProjects"
        />

        <div class="space-y-6">
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
                    v-for="project in firebaseProjects"
                    :key="project.projectId"
                    :project="project"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
