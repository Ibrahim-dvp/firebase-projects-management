<script setup>
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { router } from "@inertiajs/vue3";
import { toast } from "@/Components/ui/toast/use-toast";

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    selectedProjectId: String,
});

const deleteUser = async (uid) => {
    uid = `${uid}|${props.selectedProjectId}`;

    if (!confirm("Are you sure you want to delete this user?")) return;

    try {
        router.delete(route("users.destroy", uid), {
            preserveState: true,
            onSuccess: () => {
                toast({
                    title: "User Deleted",
                    description: `User ${uid} has been removed.`,
                });
            },
        });
    } catch (error) {
        toast({
            title: "Error",
            description: error.message || "Failed to delete user.",
            variant: "destructive",
        });
    }
};

const emit = defineEmits(["open-reset"]);
const openReset = (email, uid) => {
    emit("open-reset", { email, uid });
};
const resetPassword = (email) => {
    try {
        router.post(
            route("users.resetPassword"),
            {
                email,
                project: props.selectedProjectId,
            },
            {
                preserveState: true,
                onSuccess: () => {
                    toast({
                        title: "Reset Email Sent",
                        description: `Password reset email sent to ${email}`,
                    });
                },
            }
        );
    } catch (error) {
        toast({
            title: "Error",
            description: error.message || "Failed to send reset email.",
            variant: "destructive",
        });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return "Never";
    const date = new Date(dateString);
    return date.toLocaleDateString() + " " + date.toLocaleTimeString();
};
</script>

<template>
    <div>
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>User</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Created</TableHead>
                    <TableHead>Last Login</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="user in users" :key="user.uid">
                    <TableCell>
                        <div class="font-medium">{{ user.email }}</div>
                        <div class="text-xs text-muted-foreground">
                            {{ user.uid }}
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex gap-2">
                            <Badge
                                :variant="
                                    user.disabled ? 'destructive' : 'default'
                                "
                                class="capitalize p-1 bg-transparent border-green-400 text-green-400"
                            >
                                {{ user.disabled ? "Disabled" : "Active" }}
                            </Badge>
                            <Badge
                                v-if="!user.disabled"
                                :variant="
                                    user.emailVerified ? 'default' : 'secondary'
                                "
                                class="capitalize p-1"
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
                        {{ formatDate(user.metadata.createdAt) }}
                    </TableCell>
                    <TableCell>
                        {{ formatDate(user.metadata.lastLoginAt) }}
                    </TableCell>
                    <TableCell class="text-right space-x-2">
                        <Button
                            class="bg-red-600"
                            variant="destructive"
                            size="sm"
                            @click="deleteUser(user.uid)"
                        >
                            Delete
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="resetPassword(user.email)"
                        >
                            Reset
                        </Button>
                        <!-- <Button
                            variant="outline"
                            size="sm"
                            @click="openReset(user.email, user.uid)"
                        >
                            Reset
                        </Button> -->
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
