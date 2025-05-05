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
