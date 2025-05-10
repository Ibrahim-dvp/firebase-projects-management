<script setup>
import { ref, watch } from "vue";
import { PlusIcon, FileInputIcon, Trash2, Send } from "lucide-vue-next";
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
import SendEmailDialog from "@/Components/users/SendEmailDialog.vue";

const { toast } = useToast();
const AddUserIsOpen = ref(false);
const SendEmailsIsOpen = ref(false);

const props = defineProps({
    isLoading: Boolean,
    selectedProjectId: String,
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
        project: string().required(), // Add project to validation schema
    }),
});

const [email, emailAttrs] = defineField("email");
const [password, passwordAttrs] = defineField("password");
const [project] = defineField("project"); // Define the project field

watch(
    () => props.selectedProjectId,
    (newVal) => {
        project.value = newVal;
    },
    { immediate: true }
);
const onSubmit = handleSubmit(async (values) => {
    try {
        router.post(route("users.store"), values, {
            onSuccess: () => {
                AddUserIsOpen.value = false;
                resetForm();
                toast({
                    title: "success",
                    description: "User Added!",
                    variant: "default",
                });
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

const deleteAll = () => {
    try {
        router.delete(route("users.deleteAll", props.selectedProjectId), {
            preserveState: true,
            onSuccess: () => {
                toast({
                    title: "Success",
                    description: `All Users deleted`,
                });
            },
        });
    } catch (error) {
        toast({
            title: "Error",
            description: error.message || "Failed to delete all users.",
            variant: "destructive",
        });
    }
};
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
            <SendEmailDialog :project="selectedProjectId" />
            <Button size="sm" @click="AddUserIsOpen = true">
                <PlusIcon class="h-4 w-4 mr-2" />
                Add User
            </Button>
            <Button size="sm" class="bg-red-600 text-white" @click="deleteAll">
                <Trash2 class="h-4 w-4 mr-2" />
                Delete All
            </Button>
            <Dialog v-model:open="AddUserIsOpen">
                <DialogTrigger as-child> </DialogTrigger>
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
                        <input
                            type="hidden"
                            name="project"
                            :value="selectedProjectId"
                        />

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
                                @click="AddUserIsOpen = false"
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
I want to seperate also the add user dialague in separate component as I did
with send Emails:
<script setup>
import { ref } from "vue";
import { Send, Loader2 } from "lucide-vue-next";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Button } from "@/Components/ui/button";
import { Textarea } from "@/Components/ui/textarea";

import { useForm } from "vee-validate";
import { object, string } from "yup";
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
        subject: string().required("Subject is required"),
        body: string().required("Body content is required"),
        senderEmail: string().required("Sender email is required").email(),
        senderName: string().required("Sender name is required"),
        replyTo: string().email("Must be a valid email").optional(),
    }),
    initialValues: {
        senderEmail: "noreply@yourdomain.com",
        senderName: "Your App Name",
        replyTo: "support@yourdomain.com",
    },
});

const [subject] = defineField("subject");
const [body] = defineField("body");
const [senderEmail] = defineField("senderEmail");
const [senderName] = defineField("senderName");
const [replyTo] = defineField("replyTo");

const onSubmit = handleSubmit(async (values) => {
    isLoading.value = true;
    try {
        router.post(
            route("users.send-reset-emails"),
            {
                ...values,
                project: props.project,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    toast({
                        title: "Success",
                        description:
                            "Emails sent successfully with updated template!",
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
            <Button variant="outline">
                <Send class="h-4 w-4 mr-2" />
                Send Emails
            </Button>
        </DialogTrigger>

        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>Customize & Send Reset Emails</DialogTitle>
                <DialogDescription>
                    Update the email template and send password reset links to
                    users
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="onSubmit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="senderEmail">Sender Email</Label>
                        <Input id="senderEmail" v-model="senderEmail" />
                        <p class="text-sm text-red-600">
                            {{ errors.senderEmail }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="senderName">Sender Name</Label>
                        <Input id="senderName" v-model="senderName" />
                        <p class="text-sm text-red-600">
                            {{ errors.senderName }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="replyTo">Reply-To Email</Label>
                    <Input id="replyTo" v-model="replyTo" />
                    <p class="text-sm text-red-600">{{ errors.replyTo }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="subject">Email Subject</Label>
                    <Input id="subject" v-model="subject" />
                    <p class="text-sm text-red-600">{{ errors.subject }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="body">Email Body</Label>
                    <Textarea
                        id="body"
                        v-model="body"
                        class="hover:border-chart-1 focus:ring-chart-1 focus:ring-offset-1"
                        placeholder="Enter email content..."
                    />
                    <!-- <p class="text-sm text-muted-foreground">
                        Use %APP_NAME% and %LINK% placeholders
                    </p> -->
                    <p class="text-sm text-red-600">{{ errors.body }}</p>
                </div>

                <DialogFooter>
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
                            Sending...
                        </span>
                        <span v-else>Send Emails</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
here's my sendEmailsDialague compoenent
