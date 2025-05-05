<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { useToast } from "@/Components/ui/toast/use-toast";
import { usePage } from "@inertiajs/vue3";

// Components
import UsersHead from "@/Components/users/UsersHead.vue";
import EmptyUserState from "@/Components/users/EmptyUserState.vue";
import UserSkeleton from "@/Components/users/UserSkeleton.vue";
import UsersFilters from "@/Components/users/UsersFilters.vue";
import UsersList from "@/Components/users/UsersList.vue";
import UsersPagination from "@/Components/users/UsersPagination.vue";
import ResetPasswordDialog from "@/Components/users/ResetPasswordDialog.vue";

const props = defineProps({
    users: Array,
    pagination: Object,
    filters: Object,
    firebaseProjects: Array,
    selectedProjectId: String,
});

const page = usePage();
const { toast } = useToast();

if (page.props.toast) {
    toast({
        title: page.props.toast.type === "success" ? "Success" : "Error",
        description: page.props.toast.message,
        variant:
            page.props.toast.type === "success" ? "default" : "destructive",
    });
}

const searchQuery = ref(props.filters.search || "");
const isLoading = ref(false);
const showResetDialog = ref(false);
const resetEmail = ref("");
const userId = ref("");

// catch the event from UsersList
function onOpenReset({ email, uid }) {
    resetEmail.value = email;
    userId.value = uid;
    showResetDialog.value = true;
}

// Client-side filtered users
const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;
    return props.users.filter((user) =>
        user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Handle pagination
const handlePageChange = (pageNum) => {
    isLoading.value = true;
    router.get(
        route("users.index"),
        {
            page: pageNum,
            project: props.selectedProjectId, // <— carry this through
            search: searchQuery.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
};

const refreshUsers = () => {
    isLoading.value = true;
    router.get(
        route("users.index"),
        {
            page: props.pagination.currentPage,
            project: props.selectedProjectId, // <— and here
            search: searchQuery.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Users Management" />

        <UsersHead
            :isLoading="isLoading"
            :selected-project-id="selectedProjectId"
        />

        <div class="space-y-6">
            <!-- Search and Filter -->
            <UsersFilters
                v-model:searchQuery="searchQuery"
                v-model:isLoading="isLoading"
                @refreshUsers="refreshUsers"
                :firebaseProjects="firebaseProjects"
                :selectedProject="selectedProjectId"
            />

            <!-- Loading State -->
            <UserSkeleton v-if="isLoading" />

            <!-- Empty State -->
            <EmptyUserState
                v-if="!isLoading && users.length === 0"
                @refreshUsers="refreshUsers"
            />

            <!-- Users List -->
            <UsersList
                v-if="!isLoading && users.length > 0"
                :users="filteredUsers"
                :selectedProjectId="selectedProjectId"
                @open-reset="onOpenReset"
            />

            <ResetPasswordDialog
                v-model="showResetDialog"
                :email="resetEmail"
                :uid="userId"
                :project="selectedProjectId"
            />

            <UsersPagination
                v-if="!isLoading && pagination.total > pagination.perPage"
                :pagination="{
                    total: pagination.total,
                    perPage: pagination.perPage,
                    currentPage: pagination.currentPage,
                    lastPage: pagination.lastPage,
                }"
                @page-change="handlePageChange"
            />
        </div>
    </AuthenticatedLayout>
</template>
