<!-- @/Components/users/AddUserDialog.vue -->
<script setup>
import { ref } from "vue";
import { PlusIcon, Loader2 } from "lucide-vue-next";
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
import { Button } from "@/Components/ui/button";
import { Switch } from "@/Components/ui/switch";
import { useForm } from "vee-validate";
import { object, string, boolean } from "yup";
import { useToast } from "@/Components/ui/toast/use-toast";
import { router } from "@inertiajs/vue3";

const { toast } = useToast();
const isOpen = ref(false);
const isLoading = ref(false);

const props = defineProps({
    project: {
        type: String,
        required: true,
    },
});

const { errors, handleSubmit, defineField, resetForm } = useForm({
    validationSchema: object({
        email: string()
            .required("Email is required")
            .email("Invalid email format"),
        password: string()
            .required("Password is required")
            .min(6, "Password must be at least 6 characters"),
        sendEmailVerification: boolean().default(false),
    }),
});

const [email] = defineField("email");
const [password] = defineField("password");
const [sendEmailVerification] = defineField("sendEmailVerification");

const onSubmit = handleSubmit(async (values) => {
    isLoading.value = true;
    try {
        router.post(
            route("users.store"),
            {
                ...values,
                project: props.project,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast({
                        title: "Success",
                        description: "User added successfully!",
                        variant: "default",
                    });
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
            }
        );
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
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
                <DialogDescription>
                    <p class="text-sm text-muted-foreground">
                        Create a new user in your Firebase project
                    </p>
                    <p class="text-sm text-muted-foreground">
                        The user will receive an email verification link
                    </p>
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="onSubmit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="email"
                        type="email"
                        placeholder="user@example.com"
                    />
                    <p class="text-sm text-red-600">{{ errors.email }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        v-model="password"
                        type="password"
                        placeholder="••••••"
                    />
                    <p class="text-sm text-red-600">{{ errors.password }}</p>
                </div>

                <div class="flex items-center space-x-2">
                    <Switch
                        id="sendEmailVerification"
                        v-model="sendEmailVerification"
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
                    <Button type="submit" :disabled="isLoading">
                        <span v-if="isLoading" class="flex items-center">
                            <Loader2 class="h-4 w-4 mr-2 animate-spin" />
                            Creating...
                        </span>
                        <span v-else>Create User</span>
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
