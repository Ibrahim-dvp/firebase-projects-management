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
} from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Skeleton } from "@/Components/ui/skeleton";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Alert, AlertDescription } from "@/Components/ui/alert";

const props = defineProps({
    googleAccounts: {
        type: Array,
        default: () => [],
    },
    firebaseProjects: {
        type: Array,
        default: () => [],
    },
});

// Reactive state
const selectedProjectId = ref("");
const searchQuery = ref("");
const users = ref([]);
const isLoading = ref(false);
const error = ref(null);

// Fetch users from Firebase Auth
const fetchUsers = async () => {
    if (!selectedProjectId.value) return;

    isLoading.value = true;
    error.value = null;

    try {
        // You'll need to implement this API endpoint in your Laravel backend
        const response = await axios.get(
            `/api/firebase/projects/${selectedProjectId.value}/users`,
            {
                params: {
                    search: searchQuery.value,
                },
            }
        );

        users.value = response.data.users || [];
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to fetch users";
        console.error("Error fetching users:", err);
    } finally {
        isLoading.value = false;
    }
};

// Refresh users
const refreshUsers = () => fetchUsers();

// Lifecycle
onMounted(() => {
    if (props.firebaseProjects.length > 0) {
        selectedProjectId.value = props.firebaseProjects[0].projectId;
        fetchUsers();
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Firebase Users" />

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Authentication Users</CardTitle>
                    <CardDescription>
                        Manage email/password users for your Firebase projects
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Project Selection and Search -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <Select
                            v-model="selectedProjectId"
                            @update:modelValue="fetchUsers"
                        >
                            <SelectTrigger class="w-[280px]">
                                <SelectValue placeholder="Select a project" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="project in firebaseProjects"
                                    :key="project.projectId"
                                    :value="project.projectId"
                                >
                                    {{
                                        project.displayName || project.projectId
                                    }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <div class="relative flex-1">
                            <Input
                                v-model="searchQuery"
                                placeholder="Search users by email..."
                                @keyup.enter="fetchUsers"
                            />
                            <Button
                                variant="ghost"
                                size="icon"
                                class="absolute right-2 top-1/2 -translate-y-1/2"
                                @click="fetchUsers"
                            >
                                <SearchIcon class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <Alert v-if="error" variant="destructive">
                        <AlertDescription>
                            {{ error }}
                        </AlertDescription>
                    </Alert>

                    <!-- Loading State -->
                    <div v-if="isLoading" class="space-y-4">
                        <Skeleton class="h-10 w-full" />
                        <Skeleton class="h-10 w-full" />
                        <Skeleton class="h-10 w-full" />
                    </div>

                    <!-- Users Table -->
                    <Table v-if="!isLoading && users.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Email</TableHead>
                                <TableHead>UID</TableHead>
                                <TableHead>Verified</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users" :key="user.uid">
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell class="font-mono text-xs">
                                    {{ user.uid }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            user.emailVerified
                                                ? 'default'
                                                : 'secondary'
                                        "
                                    >
                                        {{
                                            user.emailVerified
                                                ? "Verified"
                                                : "Unverified"
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{
                                        new Date(
                                            user.metadata.creationTime
                                        ).toLocaleString()
                                    }}
                                </TableCell>
                                <TableCell>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 px-2"
                                    >
                                        <MoreHorizontalIcon class="h-4 w-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div
                        v-if="
                            !isLoading &&
                            users.length === 0 &&
                            selectedProjectId
                        "
                        class="flex flex-col items-center justify-center py-12 text-center"
                    >
                        <UsersIcon
                            class="h-12 w-12 text-muted-foreground mb-4"
                        />
                        <h3 class="text-lg font-medium">No users found</h3>
                        <p class="text-sm text-muted-foreground">
                            {{
                                searchQuery
                                    ? `No users match "${searchQuery}"`
                                    : "This project has no authentication users yet"
                            }}
                        </p>
                        <Button
                            variant="outline"
                            class="mt-4"
                            @click="refreshUsers"
                        >
                            Refresh
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
