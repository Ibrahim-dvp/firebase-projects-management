<script setup>
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";
const props = defineProps({
    open: Boolean,
    title: String,
    description: String,
});
const emit = defineEmits(["update:open", "confirm"]);

function handleConfirm() {
    emit("confirm");
    emit("update:open", false);
}
</script>
<template>
    <AlertDialog :open="open" @update:open="$emit('update:open', $event)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <Button variant="outline" @click="$emit('update:open', false)">
                    Cancel
                </Button>
                <Button variant="destructive" @click="handleConfirm"
                    >Confirm</Button
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
