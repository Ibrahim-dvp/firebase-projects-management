<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import { useToast } from "@/Components/ui/toast/use-toast";
import { ref } from "vue";

// ShadCN-Vue components
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Badge } from "@/Components/ui/badge";
import { Label } from "@/Components/ui/label";
import {
    Table,
    TableHeader,
    TableRow,
    TableHead,
    TableBody,
    TableCell,
} from "@/Components/ui/table";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogAction,
    AlertDialogCancel,
} from "@/Components/ui/alert-dialog";

const props = defineProps({
    users: Array,
    auth: Object,
});

const { toast } = useToast();
const currentUser = props.auth.user;

// Form state
const form = ref({
    name: "",
    email: "",
    password: "",
    role: "user",
});

// Dialog states
const isAddDialogOpen = ref(false);
const userToDelete = ref(null);

// Create user
function createUser() {
    router.post(route("admin.users.store"), form.value, {
        preserveScroll: true,
        onSuccess() {
            toast({
                title: "Success",
                description: `User ${form.value.name} has been created.`,
            });
            isAddDialogOpen.value = false;
            form.value = {
                name: "",
                email: "",
                password: "",
                role: "user",
            };
        },
        onError(errors) {
            toast({
                title: "Error",
                description: Object.values(errors).join(" "),
                variant: "destructive",
            });
        },
    });
}

// Delete user
function deleteUser() {
    if (!userToDelete.value) return;

    router.delete(route("admin.users.destroy", userToDelete.value.id), {
        preserveScroll: true,
        onSuccess() {
            toast({
                title: "Success",
                description: `User ${userToDelete.value.name} has been deleted.`,
            });
            userToDelete.value = null;
        },
        onError() {
            toast({
                title: "Error",
                description: "Failed to delete user.",
                variant: "destructive",
            });
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="User Management" />

        <div class="space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>User Management</CardTitle>
                        <CardDescription>
                            Manage all registered users in the system
                        </CardDescription>
                    </div>

                    <Dialog v-model:open="isAddDialogOpen">
                        <DialogTrigger as-child>
                            <Button> Add New User </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Create New User</DialogTitle>
                                <DialogDescription>
                                    Add a new user to the system. All fields are
                                    required.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-4 py-4">
                                <div class="space-y-2">
                                    <Label for="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="John Doe"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Email</Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="user@example.com"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="password">Password</Label>
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        placeholder="••••••••"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="role">Role</Label>
                                    <Select v-model="form.role">
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select a role"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="user"
                                                >User</SelectItem
                                            >
                                            <SelectItem value="admin"
                                                >Admin</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <DialogFooter>
                                <Button type="submit" @click="createUser">
                                    Create User
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </CardHeader>

                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users" :key="user.id">
                                <TableCell class="font-medium">
                                    {{ user.name }}
                                    <span
                                        v-if="user.id === currentUser.id"
                                        class="ml-2 text-xs text-muted-foreground"
                                    >
                                        (You)
                                    </span>
                                </TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <Badge
                                        class="p-1"
                                        variant="outline"
                                        :class="{
                                            'bg-purple-100 dark:bg-purple-900':
                                                user.role === 'admin',
                                            'bg-blue-100 dark:bg-blue-900':
                                                user.role === 'user',
                                        }"
                                    >
                                        {{ user.role }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <AlertDialog
                                        v-if="user.id !== currentUser.id"
                                    >
                                        <AlertDialogTrigger as-child>
                                            <Button
                                                class="bg-red-600"
                                                variant="destructive"
                                                size="sm"
                                                @click="userToDelete = user"
                                            >
                                                Delete
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle
                                                    >Are you absolutely
                                                    sure?</AlertDialogTitle
                                                >
                                                <AlertDialogDescription>
                                                    This action cannot be
                                                    undone. This will
                                                    permanently delete the user
                                                    account and remove all their
                                                    data from our servers.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel
                                                    >Cancel</AlertDialogCancel
                                                >
                                                <AlertDialogAction
                                                    variant="destructive"
                                                    @click="deleteUser"
                                                >
                                                    Delete User
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                    >
                                        Current user
                                    </span>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="users.length === 0">
                                <TableCell colspan="4" class="text-center h-24">
                                    No users found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
