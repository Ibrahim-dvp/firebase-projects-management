<script setup>
import { ref } from "vue";

import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { useForm } from "vee-validate";
import { object, string } from "yup";
import useFirebaseProjects from "@/Composables/useFirebaseProjects";
import { useToast } from "@/Components/ui/toast/use-toast";

const props = defineProps({
    googleAccounts: {
        type: Array,
        default: () => [],
    },
    open: Boolean,
});

const { toast } = useToast();
const { createProject } = useFirebaseProjects();
const processingToast = ref(null);
const emit = defineEmits(["update:open"]);

const { errors, handleSubmit, defineField, resetForm } = useForm({
    validationSchema: object({
        accountId: string().required("Google account is required"),
        projectId: string()
            .required("Project ID is required")
            .max(30)
            .min(6)
            .matches(
                /^[a-z][a-z0-9-]*$/,
                "Must start with letter and contain only lowercase letters, numbers and hyphens"
            ),
        displayName: string().required("Display name is required"),
    }),
});

const [accountId, accountIdAttrs] = defineField("accountId");
const [projectId, projectIdAttrs] = defineField("projectId");
const [displayName, displayNameAttrs] = defineField("displayName");

const isLoading = ref(false);

const onSubmit = handleSubmit(async (values) => {
    try {
        handleProjectAdded();
        const account = props.googleAccounts.find(
            (acc) => acc.id === values.accountId
        );
        if (!account) throw new Error("Selected account not found");
        emit("update:open", false);
        const result = await createProject(account, {
            projectId: values.projectId,
            displayName: values.displayName,
        });
        // console.log("Project created:", result);
        if (result.success) {
            handleSuccess();
            console.log(result);
        } else {
            handleError(result.error);
            console.log(result.error);
        }
    } catch (error) {
        console.error("Project creation error:", error);
        emit("error", {
            message: error.message || "Failed to create project",
            details: error.details,
        });
    } finally {
        resetForm();
    }
});

const handleProjectAdded = () => {
    processingToast.value = toast({
        title: "Processing",
        description: "Creating your Firebase project...",
        variant: "default",
        duration: Infinity, // Don't auto-dismiss
    });
};

const handleSuccess = () => {
    if (processingToast.value) {
        processingToast.value.dismiss();
        processingToast.value = null;
    }
    toast({
        title: "Success",
        description: "Firebase project created successfully",
        variant: "success",
    });
    setTimeout(() => {
        emit("refreshProjects");
    }, 7000);
};

const handleError = (error) => {
    if (processingToast.value) {
        processingToast.value.dismiss();
        processingToast.value = null;
    }
    toast({
        title: "Error",
        description: error.message || "Failed to add project",
        variant: "destructive",
    });
};
</script>
<template>
    <form @submit.prevent="onSubmit" class="space-y-4">
        <div class="space-y-2">
            <Label for="account">Google Account</Label>
            <Select
                id="account"
                name="accountId"
                v-model="accountId"
                v-bind="accountIdAttrs"
            >
                <SelectTrigger>
                    <SelectValue
                        v-model="accountId"
                        placeholder="Select account"
                    />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        id="accountId"
                        v-for="account in googleAccounts"
                        :key="account.id"
                        :value="account.id"
                    >
                        {{ account.email }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <div class="text-sm text-red-600 ml-2">
                {{ errors.accountId }}
            </div>
        </div>

        <div class="space-y-2">
            <Label for="projectId">Project ID</Label>
            <Input
                id="projectId"
                v-model="projectId"
                v-bind="projectIdAttrs"
                name="projectId"
                placeholder="unique-project-id"
            />
            <div class="text-sm text-red-600 ml-2">
                {{ errors.projectId }}
            </div>
        </div>

        <div class="space-y-2">
            <Label for="displayName">Display Name</Label>
            <Input
                id="displayName"
                v-model="displayName"
                v-bind="displayNameAttrs"
                name="displayName"
                placeholder="My Awesome Project"
            />
            <div class="text-sm text-red-600 ml-2">
                {{ errors.displayName }}
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <Button
                variant="outline"
                type="button"
                @click="$emit('update:open', false)"
            >
                Cancel
            </Button>

            <Button type="submit" :disabled="isLoading">
                <span v-if="isLoading" class="flex items-center">
                    <svg
                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    Creating...
                </span>
                <span v-else>Create Project</span>
            </Button>
        </div>
    </form>
</template>
