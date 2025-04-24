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

const props = defineProps({
    users: Array,
    pagination: Object,
    filters: Object,
    googleAccounts: {
        type: Array,
        default: () => [],
    },
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

// Client-side filtered users
const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;
    return props.users.filter((user) =>
        user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Handle pagination
const handlePageChange = async (page) => {
    isLoading.value = true;
    try {
        await router.get(
            route("users.index"),
            {
                page,
                search: searchQuery.value,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    } finally {
        isLoading.value = false;
    }
};

const refreshUsers = async () => {
    isLoading.value = true;
    console.log("gg");

    try {
        router.get(
            route("users.index"),
            {
                page: props.pagination.currentPage,
                search: searchQuery.value,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Users Management" />

        <UsersHead
            :isLoading="isLoading"
            @refreshUsers="refreshUsers"
            @addUser="addUser"
        />

        <div class="space-y-6">
            <!-- Search and Filter -->
            <UsersFilters
                v-model:searchQuery="searchQuery"
                :googleAccounts="googleAccounts"
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
            />
        </div>
    </AuthenticatedLayout>
</template>
