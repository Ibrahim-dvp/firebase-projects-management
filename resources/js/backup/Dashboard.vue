<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { router } from "@inertiajs/vue3";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Badge } from "@/Components/ui/badge";
import { Skeleton } from "@/Components/ui/skeleton";

import {
    UserIcon,
    PlusIcon,
    TrashIcon,
    CheckIcon,
    FolderIcon,
} from "lucide-vue-next";
import EmptyAccoutState from "@/Components/dashboard/EmptyAccoutState.vue";

defineProps({
    googleAccounts: {
        type: Array,
        required: true,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
});

const deleteAccount = (accountId) => {
    if (confirm("Are you sure you want to delete this account?")) {
        router.delete(`/accounts/${accountId}`, {
            onSuccess: () => {
                // Could use toast notification here
            },
        });
    }
};

const fetchFirebaseProjects = (accountId) => {
    router.visit(`/google-accounts/${accountId}/projects`);
};

const linkGoogleAccount = () => {
    window.location.href = "/auth/google";
};
</script>

<template>
    <Head title="Google Accounts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">
                    Google Accounts
                </h2>
                <Button @click="linkGoogleAccount" class="gap-2">
                    <PlusIcon class="h-4 w-4" />
                    Add Account
                </Button>
            </div>
        </template>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg"> Connected Accounts </CardTitle>
                </CardHeader>
                <CardContent>
                    <!-- Loading State -->
                    <div v-if="isLoading" class="space-y-4">
                        <div
                            v-for="i in 3"
                            :key="i"
                            class="flex items-center space-x-4 p-4 border rounded-lg"
                        >
                            <Skeleton class="h-10 w-10 rounded-full" />
                            <div class="space-y-2 flex-1">
                                <Skeleton class="h-4 w-[200px]" />
                                <Skeleton class="h-4 w-[150px]" />
                            </div>
                            <Skeleton class="h-9 w-[80px]" />
                            <Skeleton class="h-9 w-[80px]" />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <!-- <div
                        v-else-if="googleAccounts.length === 0"
                        class="flex flex-col items-center justify-center py-12 space-y-4"
                    >
                        <div
                            class="w-24 h-24 rounded-full bg-muted flex items-center justify-center"
                        >
                            <UserIcon class="w-12 h-12 text-muted-foreground" />
                        </div>
                        <h3 class="text-lg font-medium">No Google accounts</h3>
                        <p class="text-sm text-muted-foreground text-center">
                            You haven't connected any Google accounts yet
                        </p>
                        <Button @click="linkGoogleAccount" class="mt-2">
                            Connect Account
                        </Button>
                    </div> -->
                    <EmptyAccoutState
                        v-else-if="googleAccounts.length === 0"
                        @linkGoogleAccount="linkGoogleAccount"
                    />
                    <!-- Accounts Table -->
                    <div v-else class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Account</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="account in googleAccounts"
                                    :key="account.id"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-4">
                                            <Avatar class="h-9 w-9">
                                                <AvatarImage
                                                    :src="account.picture"
                                                />
                                                <AvatarFallback>
                                                    {{ account.name.charAt(0) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <p class="font-medium">
                                                    {{ account.name }}
                                                </p>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {{ account.email }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant="outline"
                                            class="text-green-600 dark:text-green-400"
                                        >
                                            <CheckIcon class="h-3 w-3 mr-1" />
                                            Connected
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="
                                                    fetchFirebaseProjects(
                                                        account.id
                                                    )
                                                "
                                                class="gap-1"
                                            >
                                                <FolderIcon
                                                    class="h-3.5 w-3.5"
                                                />
                                                Projects
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                @click="
                                                    deleteAccount(account.id)
                                                "
                                                class="gap-1"
                                            >
                                                <TrashIcon
                                                    class="h-3.5 w-3.5"
                                                />
                                                Remove
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
