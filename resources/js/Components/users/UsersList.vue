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

defineProps({
    users: {
        type: Array,
        required: true,
    },
});

const formatDate = (dateString) => {
    if (!dateString) return "Never";
    const date = new Date(dateString);
    return date.toLocaleDateString() + " " + date.toLocaleTimeString();
};
</script>

<template>
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
                            :variant="user.disabled ? 'destructive' : 'default'"
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
                            {{ user.emailVerified ? "Verified" : "Unverified" }}
                        </Badge>
                    </div>
                </TableCell>
                <TableCell>
                    {{ formatDate(user.metadata.createdAt) }}
                </TableCell>
                <TableCell>
                    {{ formatDate(user.metadata.lastLoginAt) }}
                </TableCell>
                <TableCell class="text-right">
                    <Button variant="outline" size="sm"> Manage </Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
