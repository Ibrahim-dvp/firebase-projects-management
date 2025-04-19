<script setup>
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { CheckIcon, FolderIcon, TrashIcon } from "lucide-vue-next";
import { router } from "@inertiajs/vue3";

defineProps({
    googleAccounts: {
        type: Array,
        required: true,
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
    router.visit(`/projects`, {
        method: "get",
        data: {
            accountId,
        },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>Account</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="account in googleAccounts" :key="account.id">
                <TableCell>
                    <div class="flex items-center gap-4">
                        <Avatar class="h-9 w-9">
                            <AvatarImage :src="account.picture" />
                            <AvatarFallback>
                                {{ account.name.charAt(0) }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <p class="font-medium">
                                {{ account.name }}
                            </p>
                            <p class="text-sm text-muted-foreground">
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
                            @click="fetchFirebaseProjects(account.id)"
                            class="gap-1"
                        >
                            <FolderIcon class="h-3.5 w-3.5" />
                            Projects
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="deleteAccount(account.id)"
                            class="gap-1"
                        >
                            <TrashIcon class="h-3.5 w-3.5" />
                            Remove
                        </Button>
                    </div>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
