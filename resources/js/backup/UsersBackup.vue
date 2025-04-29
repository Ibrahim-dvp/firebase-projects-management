<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Badge } from "@/Components/ui/badge";
import {
    Pagination,
    PaginationEllipsis,
    PaginationFirst,
    PaginationLast,
    PaginationNext,
    PaginationPrev,
} from "@/Components/ui/pagination";

const props = defineProps({
    users: Array,
    pagination: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const currentPage = ref(props.pagination.currentPage);

// Client-side filtering
const filteredUsers = computed(() => {
    if (!search.value) return props.users;

    return props.users.filter((user) =>
        user.email.toLowerCase().includes(search.value.toLowerCase())
    );
});

// Format date properly
const formatDate = (dateString) => {
    if (!dateString) return "Never";
    const date = new Date(dateString);
    return date.toLocaleDateString() + " " + date.toLocaleTimeString();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="User Management" />

        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col space-y-6">
                <!-- Header and Search -->
                <Card>
                    <CardHeader>
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
                        >
                            <div>
                                <CardTitle>User Management</CardTitle>
                                <p class="text-sm text-muted-foreground">
                                    Manage all authentication users in your
                                    Firebase project
                                </p>
                            </div>

                            <div class="w-full sm:w-auto">
                                <Input
                                    v-model="search"
                                    placeholder="Search users by email..."
                                    class="w-full sm:w-[300px]"
                                />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- Users Table -->
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>User</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead>Last Login</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="user in filteredUsers"
                                    :key="user.uid"
                                >
                                    <TableCell>
                                        <div class="font-medium">
                                            {{ user.email }}
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ user.uid }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-2">
                                            <Badge
                                                :variant="
                                                    user.disabled
                                                        ? 'destructive'
                                                        : 'default'
                                                "
                                                class="capitalize p-2"
                                            >
                                                {{
                                                    user.disabled
                                                        ? "Disabled"
                                                        : "Active"
                                                }}
                                            </Badge>
                                            <Badge
                                                v-if="!user.disabled"
                                                :variant="
                                                    user.emailVerified
                                                        ? 'default'
                                                        : 'secondary'
                                                "
                                                class="capitalize"
                                            >
                                                {{
                                                    user.emailVerified
                                                        ? "Verified"
                                                        : "Unverified"
                                                }}
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            formatDate(user.metadata.createdAt)
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            formatDate(
                                                user.metadata.lastLoginAt
                                            )
                                        }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            as-child
                                        >
                                            <Link
                                                :href="
                                                    route('users', {
                                                        user: user.uid,
                                                    })
                                                "
                                            >
                                                Manage
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <!-- Pagination -->
                        <Pagination
                            v-if="pagination.total > pagination.perPage"
                            class="mt-6"
                        >
                            <div class="flex items-center gap-1">
                                <PaginationFirst
                                    :href="route('users', { page: 1 })"
                                    :disabled="currentPage === 1"
                                />
                                <PaginationPrev
                                    :href="
                                        route('users', {
                                            page: currentPage - 1,
                                        })
                                    "
                                    :disabled="currentPage === 1"
                                />
                                <PaginationNext
                                    :href="route('users')"
                                    :disabled="
                                        currentPage * pagination.perPage >=
                                        pagination.total
                                    "
                                />
                                <PaginationLast
                                    :href="
                                        route('users', {
                                            page: Math.ceil(
                                                pagination.total /
                                                    pagination.perPage
                                            ),
                                        })
                                    "
                                    :disabled="
                                        currentPage * pagination.perPage >=
                                        pagination.total
                                    "
                                />
                            </div>
                            <div class="text-sm text-muted-foreground">
                                Page {{ currentPage }} of
                                {{
                                    Math.ceil(
                                        pagination.total / pagination.perPage
                                    )
                                }}
                            </div>
                        </Pagination>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
