<script setup>
import { ref } from "vue";
import { PlusIcon, RefreshCwIcon, FileInputIcon } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Switch } from "@/Components/ui/switch";
import { useForm } from "vee-validate";
import { object, string, boolean } from "yup";
import { useToast } from "@/Components/ui/toast/use-toast";
import { router } from "@inertiajs/vue3";
import { parse } from "papaparse";

const { toast } = useToast();
const isOpen = ref(false);
const fileInput = ref(null);

defineProps({
    isLoading: Boolean,
});

const { errors, handleSubmit, defineField, resetForm } = useForm({
    validationSchema: object({
        email: string()
            .required("Email is required")
            .email("Invalid email format"),
        password: string()
            .required("Password is required")
            .min(6, "Password must be at least 6 characters"),
        sendEmailVerification: boolean(),
    }),
});

const [email, emailAttrs] = defineField("email");
const [password, passwordAttrs] = defineField("password");
const onSubmit = handleSubmit(async (values) => {
    try {
        router.post(route("users.store"), values, {
            onSuccess: () => {
                isOpen.value = false;
                resetForm();
            },
            onError: (errors) => {
                toast({
                    title: "Error",
                    description: Object.values(errors).join("\n"),
                    variant: "destructive",
                });
            },
        });
    } catch (error) {
        toast({
            title: "Error",
            description: "Failed to create user",
            variant: "destructive",
        });
    }
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    parse(file, {
        header: true,
        skipEmptyLines: true,
        complete: async (results) => {
            try {
                const validUsers = results.data.filter(
                    (row) => row.email && row.password
                );

                if (validUsers.length === 0) {
                    throw new Error(
                        "CSV file must contain email and password columns"
                    );
                }

                const response = await router.post(route("users.import"), {
                    users: validUsers,
                });

                toast({
                    title: "Success",
                    description: `Imported ${validUsers.length} users successfully!`,
                });
            } catch (error) {
                toast({
                    title: "Error",
                    description: error.message,
                    variant: "destructive",
                });
            } finally {
                // Reset file input
                if (fileInput.value) fileInput.value.value = "";
            }
        },
        error: (error) => {
            toast({
                title: "CSV Error",
                description: error.message,
                variant: "destructive",
            });
        },
    });
};

defineEmits(["refreshUsers", "addUser"]);
</script>

<template>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Users Management</h1>
            <p class="text-muted-foreground">
                Manage all authentication users in your Firebase project
            </p>
        </div>
        <div class="flex gap-2">
            <Button
                variant="outline"
                :disabled="isLoading"
                @click="$emit('refreshUsers')"
            >
                <RefreshCwIcon
                    class="h-4 w-4 mr-2"
                    :class="{ 'animate-spin': isLoading }"
                />
                Refresh
            </Button>

            <Button variant="outline" size="sm" asChild>
                <Label for="csv-upload">
                    <FileInputIcon class="h-4 w-4 mr-2" />
                    Import CSV
                    <input
                        id="csv-upload"
                        ref="fileInput"
                        type="file"
                        accept=".csv"
                        class="hidden"
                        @change="handleFileUpload"
                    />
                </Label>
            </Button>
            <Dialog v-model:open="isOpen">
                <DialogTrigger as-child>
                    <Button size="sm">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add User
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create New User</DialogTitle>
                    </DialogHeader>
                    <DialogDescription>
                        <p class="text-sm text-muted-foreground">
                            Create a new user in your Firebase project.
                        </p>
                        <p class="text-sm text-muted-foreground">
                            The user will receive an email verification link.
                        </p>
                    </DialogDescription>

                    <form @submit="onSubmit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="email"
                                v-bind="emailAttrs"
                                name="email"
                                type="email"
                                placeholder="user@example.com"
                            />
                            <div class="text-sm text-red-600 ml-2">
                                {{ errors.email }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">Password</Label>
                            <Input
                                id="password"
                                v-model="password"
                                v-bind="passwordAttrs"
                                name="password"
                                type="password"
                                placeholder="••••••"
                            />
                            <div class="text-sm text-red-600 ml-2">
                                {{ errors.password }}
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Switch
                                id="sendEmailVerification"
                                name="sendEmailVerification"
                            />
                            <Label for="sendEmailVerification"
                                >Send email verification</Label
                            >
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="isOpen = false"
                            >
                                Cancel
                            </Button>
                            <Button type="submit"> Create User </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
