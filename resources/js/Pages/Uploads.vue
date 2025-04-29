<script setup>
import { ref, onMounted, watch } from "vue";
import { useForm, router, Head, usePage } from "@inertiajs/vue3";
import { toast } from "@/Components/ui/toast/use-toast";

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/Components/ui/card";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Switch } from "@/Components/ui/switch";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import {
    UploadIcon,
    FileTextIcon,
    XIcon,
    UsersIcon,
    KeyIcon,
} from "lucide-vue-next";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import useFirebaseProjects from "@/Composables/useFirebaseProjects";

const props = defineProps({
    googleAccounts: {
        type: Array,
        default: () => [],
    },
    firebaseProjects: {
        type: Array,
        default: () => [],
    },
});
const toastData = usePage().props.toast;

if (toastData) {
    toast({
        title: toastData.type === "success" ? "Success" : "Error",
        description: toastData.message,
        variant: toastData.type === "success" ? "default" : "destructive",
    });
}
const { firebaseProjects, fetchProjects } = useFirebaseProjects(
    props.googleAccounts
);

// Tab state
const activeTab = ref("serviceAccount");

// Service Account Form
const serviceAccountForm = useForm({
    email: "",
    project_id: "",
    display_name: "",
    credentials_file: null,
});

// User Import Form
const userImportForm = useForm({
    target_project_id: "",
    csv_file: null,
    send_verification_emails: false,
});

// File input refs
const serviceAccountFileInput = ref(null);
const csvFileInput = ref(null);
const isDragging = ref("");

// Handle file operations
const handleFile = (form, file, type) => {
    if (type === "serviceAccount" && file.type !== "application/json") {
        form.errors.credentials_file = "Only JSON files allowed";
        return false;
    }
    if (type === "csv" && !file.name.endsWith(".csv")) {
        form.errors.csv_file = "Only CSV files allowed";
        return false;
    }
    form[type === "serviceAccount" ? "credentials_file" : "csv_file"] = file;
    return true;
};

const removeFile = (form, type) => {
    form[type === "serviceAccount" ? "credentials_file" : "csv_file"] = null;
    const input =
        type === "serviceAccount" ? serviceAccountFileInput : csvFileInput;
    if (input.value) input.value.value = "";
};

// Submit handlers
const submitServiceAccount = () => {
    serviceAccountForm.post(route("uploads.store"), {
        onError: (errors) => {
            toast({
                title: "Error",
                description: errors,
            });
        },
        onSuccess: () => {
            serviceAccountForm.reset();
            toast({
                title: "success",
                description: "Key added!",
            });
        },
    });
};

const submitUserImport = (event) => {
    if (!userImportForm.csv_file) {
        userImportForm.errors.csv_file = "CSV file is required";
        return;
    }

    userImportForm.post(route("users.import"), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset form on success
            userImportForm.reset();
            if (csvFileInput.value) csvFileInput.value.value = "";
        },
        onError: () => {
            toast({
                type: "error",
                message: "error importing",
            });
        },
    });
};

// const submitUserImport = () => {
//     userImportForm.post(route("users.import"), {
//         onSuccess: () => userImportForm.reset(),
//     });
// };
watch(
    () => serviceAccountForm.project_id,
    (newProjectId) => {
        const selected = firebaseProjects.value.find(
            (p) => p.projectId === newProjectId
        );

        serviceAccountForm.display_name = selected?.displayName || "";
    }
);
watch(
    () => serviceAccountForm.email,
    (projectsEmail) => {
        firebaseProjects.value = firebaseProjects.value.filter(
            (p) => p.accountEmail === projectsEmail
        );
    }
);

onMounted(() => {
    fetchProjects(props.googleAccounts);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Firebase Uploads" />

        <div class="container mx-auto py-8">
            <Card class="max-w-3xl mx-auto">
                <CardHeader>
                    <CardTitle>Firebase Management</CardTitle>
                    <CardDescription>
                        Upload service accounts or import users in bulk
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Tabs v-model="activeTab" class="w-full">
                        <TabsList class="grid w-full grid-cols-2">
                            <TabsTrigger value="serviceAccount">
                                <KeyIcon class="h-4 w-4 mr-2" />
                                Service Account
                            </TabsTrigger>
                            <TabsTrigger value="userImport">
                                <UsersIcon class="h-4 w-4 mr-2" />
                                User Import
                            </TabsTrigger>
                        </TabsList>

                        <!-- Service Account Tab -->
                        <TabsContent value="serviceAccount">
                            <form
                                @submit.prevent="submitServiceAccount"
                                class="space-y-6 mt-6"
                            >
                                <div class="space-y-2">
                                    <Label for="email">Select Email</Label>
                                    <Select
                                        v-model="serviceAccountForm.email"
                                        required
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select an email"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="account in googleAccounts"
                                                :key="account.email"
                                                :value="account.email"
                                            >
                                                {{ account.email }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label for="project_id"
                                        >Select Project</Label
                                    >
                                    <Select
                                        v-model="serviceAccountForm.project_id"
                                        required
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select a project"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="project in firebaseProjects"
                                                :key="project.projectId"
                                                :value="project.projectId"
                                            >
                                                {{ project.projectId }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label>Display Name</Label>
                                    <Input
                                        v-model="
                                            serviceAccountForm.display_name
                                        "
                                        disabled
                                        readonly
                                        placeholder="My Production Project"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label>Service Account JSON</Label>
                                    <div
                                        @dragenter.prevent="
                                            isDragging = 'serviceAccount'
                                        "
                                        @dragover.prevent="
                                            isDragging = 'serviceAccount'
                                        "
                                        @dragleave.prevent="isDragging = ''"
                                        @drop.prevent="
                                            handleFile(
                                                serviceAccountForm,
                                                $event.dataTransfer.files[0],
                                                'serviceAccount'
                                            )
                                        "
                                        @click="
                                            serviceAccountFileInput?.click()
                                        "
                                        class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors"
                                        :class="{
                                            'border-primary bg-primary/10':
                                                isDragging === 'serviceAccount',
                                            'border-input':
                                                !serviceAccountForm.credentials_file,
                                            'border-green-500':
                                                serviceAccountForm.credentials_file,
                                        }"
                                    >
                                        <input
                                            ref="serviceAccountFileInput"
                                            type="file"
                                            accept=".json"
                                            class="hidden"
                                            @change="
                                                handleFile(
                                                    serviceAccountForm,
                                                    $event.target.files[0],
                                                    'serviceAccount'
                                                )
                                            "
                                        />

                                        <div
                                            v-if="
                                                !serviceAccountForm.credentials_file
                                            "
                                        >
                                            <UploadIcon
                                                class="mx-auto h-10 w-10 text-muted-foreground"
                                            />
                                            <p class="mt-2 font-medium">
                                                Drag your service account JSON
                                                here
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground mt-1"
                                            >
                                                Must be a valid Firebase service
                                                account file
                                            </p>
                                        </div>

                                        <div
                                            v-else
                                            class="flex items-center justify-center gap-3"
                                        >
                                            <FileTextIcon
                                                class="h-10 w-10 text-green-500"
                                            />
                                            <div class="text-left">
                                                <p class="font-medium truncate">
                                                    {{
                                                        serviceAccountForm
                                                            .credentials_file
                                                            .name
                                                    }}
                                                </p>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {{
                                                        (
                                                            serviceAccountForm
                                                                .credentials_file
                                                                .size / 1024
                                                        ).toFixed(1)
                                                    }}
                                                    KB
                                                </p>
                                            </div>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                class="ml-auto"
                                                @click.stop="
                                                    removeFile(
                                                        serviceAccountForm,
                                                        'serviceAccount'
                                                    )
                                                "
                                            >
                                                <XIcon class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <p
                                        v-if="
                                            serviceAccountForm.errors
                                                .credentials_file
                                        "
                                        class="text-sm text-destructive"
                                    >
                                        {{
                                            serviceAccountForm.errors
                                                .credentials_file
                                        }}
                                    </p>
                                </div>

                                <Button
                                    type="submit"
                                    :disabled="serviceAccountForm.processing"
                                    class="w-full"
                                >
                                    Save Service Account
                                </Button>
                            </form>
                        </TabsContent>

                        <!-- User Import Tab -->
                        <TabsContent value="userImport">
                            <form
                                @submit.prevent="submitUserImport"
                                class="space-y-6 mt-6"
                            >
                                <div class="space-y-2">
                                    <Label>Target Project</Label>
                                    <Select
                                        v-model="
                                            userImportForm.target_project_id
                                        "
                                        required
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select a project"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="project in props.firebaseProjects"
                                                :key="project.id"
                                                :value="project.project_id"
                                            >
                                                {{ project.name }} ({{
                                                    project.project_id
                                                }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label>User CSV File</Label>
                                    <div
                                        @dragenter.prevent="isDragging = 'csv'"
                                        @dragover.prevent="isDragging = 'csv'"
                                        @dragleave.prevent="isDragging = ''"
                                        @drop.prevent="
                                            handleFile(
                                                userImportForm,
                                                $event.dataTransfer.files[0],
                                                'csv'
                                            )
                                        "
                                        @click="csvFileInput?.click()"
                                        class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors"
                                        :class="{
                                            'border-primary bg-primary/10':
                                                isDragging === 'csv',
                                            'border-input':
                                                !userImportForm.csv_file,
                                            'border-green-500':
                                                userImportForm.csv_file,
                                        }"
                                    >
                                        <input
                                            ref="csvFileInput"
                                            type="file"
                                            accept=".csv"
                                            class="hidden"
                                            @change="
                                                handleFile(
                                                    userImportForm,
                                                    $event.target.files[0],
                                                    'csv'
                                                )
                                            "
                                        />

                                        <div v-if="!userImportForm.csv_file">
                                            <UploadIcon
                                                class="mx-auto h-10 w-10 text-muted-foreground"
                                            />
                                            <p class="mt-2 font-medium">
                                                Drag your user CSV file here
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground mt-1"
                                            >
                                                Required columns: email,password
                                            </p>
                                        </div>

                                        <div
                                            v-else
                                            class="flex items-center justify-center gap-3"
                                        >
                                            <FileTextIcon
                                                class="h-10 w-10 text-green-500"
                                            />
                                            <div class="text-left">
                                                <p class="font-medium truncate">
                                                    {{
                                                        userImportForm.csv_file
                                                            .name
                                                    }}
                                                </p>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {{
                                                        (
                                                            userImportForm
                                                                .csv_file.size /
                                                            1024
                                                        ).toFixed(1)
                                                    }}
                                                    KB
                                                </p>
                                            </div>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                class="ml-auto"
                                                @click.stop="
                                                    removeFile(
                                                        userImportForm,
                                                        'csv'
                                                    )
                                                "
                                            >
                                                <XIcon class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <p
                                        v-if="userImportForm.errors.csv_file"
                                        class="text-sm text-destructive"
                                    >
                                        {{ userImportForm.errors.csv_file }}
                                    </p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Switch
                                        id="send-verification"
                                        v-model="
                                            userImportForm.send_verification_emails
                                        "
                                    />
                                    <Label for="send-verification"
                                        >Send verification emails</Label
                                    >
                                </div>

                                <Button
                                    type="submit"
                                    :disabled="userImportForm.processing"
                                    class="w-full"
                                >
                                    Start User Import
                                </Button>
                            </form>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
