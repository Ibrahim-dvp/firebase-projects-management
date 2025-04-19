<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
    CardFooter,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { Skeleton } from "@/Components/ui/skeleton";
import {
    RefreshCcwIcon,
    CheckCircleIcon,
    CuboidIcon,
    DatabaseIcon,
    Server,
    GlobeIcon,
    CalendarIcon,
} from "lucide-vue-next";

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
        console.error("API Error:", err);
    } finally {
        isLoading.value = false;
    }
};

const refreshProjects = () => {
    fetchProjects();
};

onMounted(() => {
    fetchProjects();
});

// Format creation date (mock - replace with actual date from API if available)
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Firebase Projects" />

        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Firebase Projects
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage your Firebase projects across all connected
                        accounts
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="refreshProjects"
                    :disabled="isLoading"
                >
                    <RefreshCcwIcon
                        class="w-4 h-4 mr-2"
                        :class="{ 'animate-spin': isLoading }"
                    />
                    Refresh
                </Button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Error Alert -->
            <Alert variant="destructive" v-if="error">
                <AlertTitle>Error fetching projects</AlertTitle>
                <AlertDescription>
                    {{ error }}
                    <Button
                        variant="link"
                        size="sm"
                        class="h-auto p-0 ml-1"
                        @click="refreshProjects"
                    >
                        Try again
                    </Button>
                </AlertDescription>
            </Alert>

            <!-- Loading State -->
            <div
                v-if="isLoading"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Card v-for="i in 3" :key="i">
                    <CardHeader class="space-y-2">
                        <Skeleton class="h-5 w-3/4" />
                        <Skeleton class="h-4 w-1/2" />
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="space-y-2">
                            <Skeleton class="h-4 w-1/3" />
                            <Skeleton class="h-4 w-full" />
                        </div>
                        <div class="space-y-2">
                            <Skeleton class="h-4 w-1/3" />
                            <div class="flex gap-2">
                                <Skeleton class="h-5 w-16 rounded-full" />
                                <Skeleton class="h-5 w-16 rounded-full" />
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Skeleton class="h-4 w-24" />
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty State -->
            <Card
                v-if="!isLoading && firebaseProjects.length === 0"
                class="text-center"
            >
                <CardContent class="pt-6 pb-8">
                    <div
                        class="mx-auto w-24 h-24 rounded-full bg-muted flex items-center justify-center mb-4"
                    >
                        <CuboidIcon class="w-10 h-10 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-medium mb-2">
                        No Firebase projects
                    </h3>
                    <p class="text-sm text-muted-foreground mb-4">
                        You don't have any Firebase projects in this account
                        yet.
                    </p>
                    <Button @click="refreshProjects">
                        <RefreshCcwIcon class="w-4 h-4 mr-2" />
                        Refresh projects
                    </Button>
                </CardContent>
            </Card>

            <!-- Projects Grid -->
            <div
                v-if="!isLoading && firebaseProjects.length > 0"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="project in firebaseProjects"
                    :key="project.projectId"
                    class="hover:shadow-md transition-shadow"
                >
                    <CardHeader class="pb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <CuboidIcon class="w-5 h-5 text-primary" />
                                    <span class="truncate">{{
                                        project.displayName || "Unnamed Project"
                                    }}</span>
                                </CardTitle>
                                <CardDescription class="mt-1 truncate">
                                    ID: {{ project.projectId }}
                                </CardDescription>
                            </div>
                            <Badge
                                variant="outline"
                                class="flex items-center"
                                :class="{
                                    'text-green-600 dark:text-green-400':
                                        project.state === 'ACTIVE',
                                    'text-yellow-600 dark:text-yellow-400':
                                        project.state === 'PENDING',
                                    'text-red-600 dark:text-red-400':
                                        project.state === 'DELETED',
                                }"
                            >
                                <CheckCircleIcon class="w-3 h-3 mr-1" />
                                {{ project.state || "ACTIVE" }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <p class="text-xs text-muted-foreground">
                                        Project Number
                                    </p>
                                    <p class="text-sm font-mono">
                                        {{ project.projectNumber }}
                                    </p>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-muted-foreground">
                                        Created
                                    </p>
                                    <p class="text-sm flex items-center gap-1">
                                        <CalendarIcon class="w-3.5 h-3.5" />
                                        {{ formatDate(new Date()) }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground mb-2">
                                    Services
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-if="project.resources?.hostingSite"
                                        variant="outline"
                                        class="flex items-center gap-1"
                                    >
                                        <GlobeIcon class="w-3 h-3" />
                                        Hosting
                                    </Badge>
                                    <Badge
                                        v-if="
                                            project.resources
                                                ?.realtimeDatabaseInstance
                                        "
                                        variant="outline"
                                        class="flex items-center gap-1"
                                    >
                                        <DatabaseIcon class="w-3 h-3" />
                                        Database
                                    </Badge>
                                    <Badge
                                        v-if="project.resources?.storageBucket"
                                        variant="outline"
                                        class="flex items-center gap-1"
                                    >
                                        <Server class="w-3 h-3" />
                                        Storage
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="border-t pt-3">
                        <Button variant="outline" size="sm" class="w-full">
                            View project details
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
