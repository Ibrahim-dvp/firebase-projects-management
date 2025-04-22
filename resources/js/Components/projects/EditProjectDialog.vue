<script setup>
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useForm } from "vee-validate";
import * as yup from "yup";
import { watch } from "vue";
import axios from "axios";

const props = defineProps({
    open: Boolean,
    project: Object,
    accountToken: String,
});
const emit = defineEmits(["update:open", "success", "error", "processing"]);

const { errors, handleSubmit, resetForm, defineField } = useForm({
    validationSchema: yup.object({
        displayName: yup
            .string()
            .required("Display name is required")
            .min(3, "Display name must be at least 3 characters"),
        projectId: yup.string().required("Project ID is required"),
    }),
});

const [displayName, displayNameAttrs] = defineField("displayName");
const [projectId, projectIdAttrs] = defineField("projectId");

watch(
    () => props.project,
    (project) => {
        if (project) {
            resetForm({
                values: {
                    displayName: project.displayName || "",
                    projectId: project.projectId || "",
                },
            });
        }
    },
    { immediate: true }
);

const handleSubmittedForm = handleSubmit(async (values) => {
    emit("processing");
    try {
        await axios.patch(
            `https://firebase.googleapis.com/v1beta1/projects/${props.project.projectId}`,
            { displayName: values.displayName },
            {
                headers: {
                    Authorization: `Bearer ${props.accountToken}`,
                    "Content-Type": "application/json",
                },
                params: { updateMask: "displayName" },
            }
        );

        emit("success");
        emit("update:open", false);
    } catch (error) {
        emit("error", {
            message: error.response?.data?.error?.message || "Update failed",
        });
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Edit Project</DialogTitle>
                <DialogDescription>
                    Make changes to your project information.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmittedForm" class="space-y-4">
                <div>
                    <Input
                        id="displayName"
                        v-model="displayName"
                        v-bind="displayNameAttrs"
                        placeholder="Project Name"
                    />
                    <p v-if="errors.displayName" class="text-sm text-red-600">
                        {{ errors.displayName }}
                    </p>
                </div>

                <div>
                    <!-- v-model="projectId" -->
                    <Input
                        id="projectId"
                        disabled
                        readonly
                        :model-value="projectId"
                        v-bind="projectIdAttrs"
                        placeholder="Project ID"
                        class="bg-zinc-900 cursor-not-allowed"
                    />
                </div>
                <DialogFooter class="mt-4">
                    <Button type="submit"> Save Changes </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
