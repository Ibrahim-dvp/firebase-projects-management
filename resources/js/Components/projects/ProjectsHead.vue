<script setup>
import { Button } from "@/Components/ui/button";
import { RefreshCcwIcon } from "lucide-vue-next";
import AddFirebaseProjectDialog from "@/Components/projects/AddFirebaseProjectDialog.vue";
import { ref } from "vue";

defineProps({
    isLoading: {
        type: Boolean,
        default: false,
    },
    googleAccounts: {
        type: Array,
        default: () => [],
    },
});

const isDialogOpen = ref(false);

const emit = defineEmits(["refreshProjects"]);
// const refreshProjects = ()=>{
//     em
// }
</script>
<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">
                    Firebase Projects
                </h2>
                <p class="text-sm text-muted-foreground mt-1">
                    Manage your Firebase projects across all connected accounts
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <Button @click="isDialogOpen = true" :disabled="isLoading">
                    Add firebase project
                </Button>
                <Button
                    variant="outline"
                    @click="$emit('refreshProjects')"
                    :disabled="isLoading"
                >
                    <RefreshCcwIcon
                        class="w-4 h-4 mr-2"
                        :class="{ 'animate-spin': isLoading }"
                    />
                    Refresh
                </Button>
            </div>
        </div>
        <AddFirebaseProjectDialog
            v-model:open="isDialogOpen"
            :google-accounts="googleAccounts"
            @refreshProjects="$emit('refreshProjects')"
        />
    </div>
</template>
