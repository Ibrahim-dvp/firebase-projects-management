<script setup>
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogDescription,
    DialogTitle,
} from "@/Components/ui/dialog";
import AddFirebaseProjectDialogForm from "@/Components/projects/AddFirebaseProjectDialogForm.vue";

const props = defineProps({
    open: Boolean,
    googleAccounts: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["refreshProjects", "update:open"]);

const handleOpenChange = (open) => {
    emit("update:open", open);
};
</script>

<template>
    <Dialog :open="open" @update:open="(value) => $emit('update:open', value)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add New Firebase Project</DialogTitle>
            </DialogHeader>
            <DialogDescription>
                <p class="text-sm text-muted-foreground">
                    Create a new Firebase project using your Google account.
                </p>
                <p class="text-sm text-muted-foreground">
                    Make sure to use a unique project ID.
                </p>
            </DialogDescription>
            <AddFirebaseProjectDialogForm
                :googleAccounts="googleAccounts"
                @update:open="handleOpenChange"
            />
        </DialogContent>
    </Dialog>
</template>
