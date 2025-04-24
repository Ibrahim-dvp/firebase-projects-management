<script setup>
import { ref, computed } from "vue";
import { useToast } from "@/Components/ui/toast/use-toast";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
    CardFooter,
} from "@/Components/ui/card";
import {
    CheckCircleIcon,
    CuboidIcon,
    DatabaseIcon,
    Server,
    GlobeIcon,
    CalendarIcon,
} from "lucide-vue-next";
import ConfirmDialog from "./ConfirmDialog.vue";
import EditProjectDialog from "./EditProjectDialog.vue";
import axios from "axios";

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    googleAccounts: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:open", "refreshProjects"]);
const { toast } = useToast();

const editOpen = ref(false);
const deleteOpen = ref(false);
const processingToast = ref(null);

const selectedProject = ref({
    displayName: props.project.displayName,
    projectId: props.project.projectId,
});

const accountToken = computed(
    () =>
        props.googleAccounts.find(
            (acc) => acc.email === props.project.accountEmail
        )?.access_token
);

const handleEditSuccess = () => {
    if (processingToast.value) {
        processingToast.value.dismiss();
        processingToast.value = null;
    }
    toast({ title: "Success", description: "Project updated successfully" });
    setTimeout(() => {
        emit("refreshProjects");
    }, 12000);
};

const handleEditError = (error) => {
    if (processingToast.value) {
        processingToast.value.dismiss();
        processingToast.value = null;
    }
    toast({
        title: "Error",
        description: error.message || "Failed to update project",
        variant: "destructive",
    });
};

const handleProjectEdited = () => {
    processingToast.value = toast({
        title: "Processing",
        description: "Updating your Firebase project...",
        variant: "default",
        duration: Infinity,
    });
};

const handleDelete = async () => {
    handleProjectEdited();
    if (!accountToken.value) {
        handleEditError({
            message: "No access token found for the project.",
        });
        return;
    }
    try {
        await axios.delete(
            `https://cloudresourcemanager.googleapis.com/v1/projects/${props.project.projectId}`,
            {
                headers: {
                    Authorization: `Bearer ${accountToken.value}`,
                },
            }
        );
        if (processingToast.value) {
            processingToast.value.dismiss();
            processingToast.value = null;
            toast({
                title: "Success",
                description: `Project ${props.project.projectId} deletion requested successfully.`,
            });
            setTimeout(() => {
                emit("refreshProjects");
            }, 30000);
        }
    } catch (error) {
        console.error("Error deleting project:", error);
        toast({
            title: "Error",
            description: "Failed to delete the project.",
            variant: "destructive",
        });
    } finally {
        deleteOpen.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>
<template>
    <Card class="hover:shadow-md transition-shadow">
        <CardHeader class="pb-3">
            <div class="flex justify-between items-start">
                <div>
                    <CardTitle class="flex items-center gap-2">
                        <CuboidIcon class="w-5 h-5 text-primary" />
                        <span class="truncate">{{
                            project.displayName || "Unnamed Project"
                        }}</span>
                    </CardTitle>
                    <CardDescription class="mt-1 truncate">
                        ID: {{ project.projectId }}
                    </CardDescription>
                </div>
                <Badge
                    variant="outline"
                    class="flex items-center p-1"
                    :class="{
                        'text-green-600 dark:text-green-400':
                            project.state === 'ACTIVE',
                        'text-yellow-600 dark:text-yellow-400':
                            project.state === 'PENDING',
                        'text-red-600 dark:text-red-400':
                            project.state === 'DELETED',
                    }"
                >
                    <CheckCircleIcon class="w-3 h-3 mr-1" />
                    {{ project.state || "ACTIVE" }}
                </Badge>
            </div>
        </CardHeader>
        <CardContent>
            <div class="grid gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <p class="text-xs text-muted-foreground">
                            Project Number
                        </p>
                        <p class="text-sm font-mono">
                            {{ project.projectNumber }}
                        </p>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-muted-foreground">Created</p>
                        <p class="text-sm flex items-center gap-1">
                            <CalendarIcon class="w-3.5 h-3.5" />
                            {{ formatDate(new Date()) }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground mb-2">Services</p>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-if="project.resources?.hostingSite"
                            variant="outline"
                            class="flex items-center gap-1"
                        >
                            <GlobeIcon class="w-3 h-3" />
                            Hosting
                        </Badge>
                        <Badge
                            v-if="project.resources?.realtimeDatabaseInstance"
                            variant="outline"
                            class="flex items-center gap-1"
                        >
                            <DatabaseIcon class="w-3 h-3" />
                            Database
                        </Badge>
                        <Badge
                            v-if="project.resources?.storageBucket"
                            variant="outline"
                            class="flex items-center gap-1"
                        >
                            <Server class="w-3 h-3" />
                            Storage
                        </Badge>
                    </div>
                </div>
            </div>
        </CardContent>
        <CardFooter class="border-t pt-3 space-x-2">
            <Button size="sm" class="w-full p-2" @click="editOpen = true">
                Edit Project
            </Button>
            <Button
                variant="outline"
                size="sm"
                class="w-full p-2"
                @click="deleteOpen = true"
            >
                Delete Project
            </Button>
        </CardFooter>
        <EditProjectDialog
            :open="editOpen"
            :project="selectedProject"
            :account-token="accountToken"
            @processing="handleProjectEdited"
            @success="handleEditSuccess"
            @error="handleEditError"
            @update:open="editOpen = $event"
        />

        <ConfirmDialog
            :open="deleteOpen"
            title="Delete this project?"
            description="This action cannot be undone."
            @confirm="handleDelete"
            @update:open="deleteOpen = $event"
        />
    </Card>
</template>
