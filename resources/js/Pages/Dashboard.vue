<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import EmptyAccoutState from "@/Components/dashboard/EmptyAccoutState.vue";
import GoogleAccountList from "@/Components/dashboard/GoogleAccountList.vue";
import GoogleAccountSkeleton from "@/Components/dashboard/GoogleAccountSkeleton.vue";
import GoogleAccountHead from "@/Components/dashboard/GoogleAccountHead.vue";
import DashboardHead from "@/Components/dashboard/DashboardHead.vue";

import { Head } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { Card, CardContent } from "@/Components/ui/card";

defineProps({
    googleAccounts: {
        type: Array,
        required: true,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
});

const linkGoogleAccount = () => {
    window.location.href = "/auth/google";
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Google Accounts" />
        <DashboardHead
            :isLoading="isLoading"
            @linkGoogleAccount="linkGoogleAccount"
        />
        <div class="space-y-6">
            <Card>
                <CardContent>
                    <GoogleAccountHead @linkGoogleAccount="linkGoogleAccount" />
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
