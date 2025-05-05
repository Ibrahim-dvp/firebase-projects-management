<script setup>
import { Button } from "@/Components/ui/button";
import { Trash2 } from "lucide-vue-next";
import { useToast } from "@/Components/ui/toast/use-toast";
import { router } from "@inertiajs/vue3";
import SendEmailDialog from "@/Components/users/SendEmailDialog.vue";
import AddUserDialog from "@/Components/users/AddUserDialog.vue";

const { toast } = useToast();

const props = defineProps({
    isLoading: Boolean,
    selectedProjectId: String,
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
            <AddUserDialog :project="selectedProjectId" />

            <Button size="sm" class="bg-red-600 text-white" @click="deleteAll">
                <Trash2 class="h-4 w-4 mr-2" />
                Delete All
            </Button>
        </div>
    </div>
</template>
