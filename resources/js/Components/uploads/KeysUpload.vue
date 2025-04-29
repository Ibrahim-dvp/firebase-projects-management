<script setup>
import { ref } from "vue";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";

// Service Account Form
const serviceAccountForm = useForm({
    email: "",
    project_id: "",
    display_name: "",
    credentials_file: null,
});

const submitServiceAccount = () => {
    serviceAccountForm.post(route("uploads.store"), {
        onSuccess: () => serviceAccountForm.reset(),
    });
};
</script>
<template>
    <form @submit.prevent="submitServiceAccount" class="space-y-6 mt-6">
        <div class="space-y-2">
            <Label for="email">Select Email</Label>
            <Select v-model="serviceAccountForm.email" required>
                <SelectTrigger>
                    <SelectValue placeholder="Select an email" />
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
            <Label for="project_id">Select Project</Label>
            <Select v-model="serviceAccountForm.project_id" required>
                <SelectTrigger>
                    <SelectValue placeholder="Select a project" />
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
                v-model="serviceAccountForm.display_name"
                disabled
                readonly
                placeholder="My Production Project"
            />
        </div>

        <div class="space-y-2">
            <Label>Service Account JSON</Label>
            <div
                @dragenter.prevent="isDragging = 'serviceAccount'"
                @dragover.prevent="isDragging = 'serviceAccount'"
                @dragleave.prevent="isDragging = ''"
                @drop.prevent="
                    handleFile(
                        serviceAccountForm,
                        $event.dataTransfer.files[0],
                        'serviceAccount'
                    )
                "
                @click="serviceAccountFileInput?.click()"
                class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors"
                :class="{
                    'border-primary bg-primary/10':
                        isDragging === 'serviceAccount',
                    'border-input': !serviceAccountForm.credentials_file,
                    'border-green-500': serviceAccountForm.credentials_file,
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

                <div v-if="!serviceAccountForm.credentials_file">
                    <UploadIcon
                        class="mx-auto h-10 w-10 text-muted-foreground"
                    />
                    <p class="mt-2 font-medium">
                        Drag your service account JSON here
                    </p>
                    <p class="text-sm text-muted-foreground mt-1">
                        Must be a valid Firebase service account file
                    </p>
                </div>

                <div v-else class="flex items-center justify-center gap-3">
                    <FileTextIcon class="h-10 w-10 text-green-500" />
                    <div class="text-left">
                        <p class="font-medium truncate">
                            {{ serviceAccountForm.credentials_file.name }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{
                                (
                                    serviceAccountForm.credentials_file.size /
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
                            removeFile(serviceAccountForm, 'serviceAccount')
                        "
                    >
                        <XIcon class="h-4 w-4" />
                    </Button>
                </div>
            </div>
            <p
                v-if="serviceAccountForm.errors.credentials_file"
                class="text-sm text-destructive"
            >
                {{ serviceAccountForm.errors.credentials_file }}
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
</template>
