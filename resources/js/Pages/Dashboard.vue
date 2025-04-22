<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import EmptyAccoutState from "@/Components/dashboard/EmptyAccoutState.vue";
import GoogleAccountList from "@/Components/dashboard/GoogleAccountList.vue";
import GoogleAccountSkeleton from "@/Components/dashboard/GoogleAccountSkeleton.vue";
import GoogleAccountHead from "@/Components/dashboard/GoogleAccountHead.vue";
import DashboardHead from "@/Components/dashboard/DashboardHead.vue";

import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { Card, CardContent } from "@/Components/ui/card";
import { useToast } from "@/Components/ui/toast/use-toast";

const props = defineProps({
    googleAccounts: {
        type: Array,
        required: true,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
});
const { toast } = useToast();
if (props.error) {
    toast({
        title: "Error",
        description: props.error,
        variant: "destructive",
    });
}

const linkGoogleAccount = () => {
    window.location.href = "/auth/google";
};

const refreshGoogleAccounts = () => {
    router.get(
        "/dashboard",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                console.log(
                    "Google accounts refreshed:",
                    page.props.googleAccounts
                );
            },
        }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Google Accounts" />
        <DashboardHead
            :isLoading="isLoading"
            @linkGoogleAccount="refreshGoogleAccounts"
        />
        <div class="space-y-6">
            <Card>
                <CardContent>
                    <GoogleAccountHead
                        v-if="googleAccounts.length > 0"
                        @linkGoogleAccount="linkGoogleAccount"
                    />
                    <!-- Loading State -->
                    <GoogleAccountSkeleton v-if="isLoading" />

                    <!-- Empty State -->
                    <EmptyAccoutState
                        v-else-if="googleAccounts.length === 0"
                        @linkGoogleAccount="linkGoogleAccount"
                    />
                    <!-- Accounts Table -->
                    <div v-else class="rounded-md border">
                        <GoogleAccountList :googleAccounts="googleAccounts" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
