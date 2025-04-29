<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
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
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { UploadIcon, FileTextIcon, XIcon } from "lucide-vue-next";
import useFirebaseProjects from "@/Composables/useFirebaseProjects";

const props = defineProps({
    googleAccounts: Array, // Pass fetched projects from controller
});

const { firebaseProjects, fetchProjects } = useFirebaseProjects(
    props.googleAccounts
);

const form = useForm({
    project_id: "",
    credentials_file: null,
    display_name: "",
});

const fileInput = ref(null);
const isDragging = ref(false);

const handleFileChange = (e) => {
    if (e.target.files.length) {
        processFile(e.target.files[0]);
    }
};

const handleDrop = (e) => {
    e.preventDefault();
    isDragging.value = false;
    if (e.dataTransfer.files.length) {
        processFile(e.dataTransfer.files[0]);
    }
};

const processFile = (file) => {
    if (file.type !== "application/json") {
        form.errors.credentials_file = "Only JSON files are allowed";
        return;
    }
    form.credentials_file = file;
};

const removeFile = () => {
    form.credentials_file = null;
    if (fileInput.value) fileInput.value.value = "";
};

const submit = () => {
    form.post(route("uploads.store"), {
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = "";
        },
    });
};

onMounted(() => {
    fetchProjects(props.googleAccounts);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Upload Firebase Credentials" />

        <div class="container mx-auto py-8">
            <Card class="max-w-2xl mx-auto">
                <CardHeader>
                    <CardTitle>Add Service Account Key</CardTitle>
                    <CardDescription>
                        Upload private key JSON for a Firebase project
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="project_id">Select Project</Label>
                            <Select v-model="form.project_id" required>
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
                                        {{
                                            project.DisplayName ||
                                            project.projectId
                                        }}
                                        <span
                                            class="text-muted-foreground text-xs ml-2"
                                        >
                                            ({{ project.accountEmail }})
                                        </span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label>Service Account File</Label>
                            <div
                                @dragenter.prevent="isDragging = true"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop="handleDrop"
                                @click="fileInput?.click()"
                                class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors"
                                :class="{
                                    'border-primary bg-primary/10': isDragging,
                                    'border-input':
                                        !isDragging && !form.credentials_file,
                                    'border-green-500': form.credentials_file,
                                }"
                            >
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept=".json"
                                    class="hidden"
                                    @change="handleFileChange"
                                />

                                <div v-if="!form.credentials_file">
                                    <UploadIcon
                                        class="mx-auto h-10 w-10 text-muted-foreground"
                                    />
                                    <p class="mt-2 font-medium">
                                        Drag your service account JSON here or
                                        click to browse
                                    </p>
                                    <p
                                        class="text-sm text-muted-foreground mt-1"
                                    >
                                        Only Firebase service account JSON files
                                        are accepted
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
                                            {{ form.credentials_file.name }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                (
                                                    form.credentials_file.size /
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
                                        @click.stop="removeFile"
                                    >
                                        <XIcon class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <p
                                v-if="form.errors.credentials_file"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.credentials_file }}
                            </p>
                        </div>

                        <Button
                            type="submit"
                            :disabled="form.processing || !form.project_id"
                            class="w-full"
                        >
                            <span v-if="form.processing">Uploading...</span>
                            <span v-else>Save Credentials</span>
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
