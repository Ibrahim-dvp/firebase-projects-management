<script setup>
import { Button } from "@/Components/ui/button";
import { Trash2 } from "lucide-vue-next";
import { useToast } from "@/Components/ui/toast/use-toast";
import { router } from "@inertiajs/vue3";
import SendEmailDialog from "@/Components/users/SendEmailDialog.vue";
import AddUserDialog from "@/Components/users/AddUserDialog.vue";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/Components/ui/alert-dialog";
import { ref } from "vue";

const { toast } = useToast();
const isDeleteDialogOpen = ref(false);

const props = defineProps({
    isLoading: Boolean,
    selectedProjectId: String,
});

const deleteAll = () => {
    try {
        router.delete(route("users.deleteAll", props.selectedProjectId), {
            preserveState: true,
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                toast({
                    title: "Success",
                    description: `All Users deleted`,
                });
            },
            onError: (error) => {
                toast({
                    title: "Error",
                    description: error.message || "Failed to delete all users.",
                    variant: "destructive",
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
            <AddUserDialog :project="selectedProjectId" />

            <AlertDialog v-model:open="isDeleteDialogOpen">
                <AlertDialogTrigger as-child>
                    <Button size="sm" class="bg-red-600 text-white">
                        <Trash2 class="h-4 w-4 mr-2" />
                        Delete All
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle
                            >Are you absolutely sure?</AlertDialogTitle
                        >
                        <AlertDialogDescription>
                            This action cannot be undone. This will permanently
                            delete all users in the selected Firebase project.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            @click="deleteAll"
                            class="bg-red-600 hover:bg-red-700"
                        >
                            Continue
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </div>
</template>
