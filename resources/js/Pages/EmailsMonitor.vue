<script setup>
import { ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { usePage, Head, router } from "@inertiajs/vue3";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from "@/Components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Progress } from "@/Components/ui/progress";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { RefreshCwIcon } from "lucide-vue-next";

const props = defineProps({
    batches: Object,
    statusColors: Object,
});

const batchesData = ref(props.batches.data);
const isLoading = ref(false);

// Calculate progress for each batch
const calculateProgress = (batch) => {
    if (!batch.total_emails || batch.total_emails === 0) return 0;
    return Math.round(
        ((batch.sent_count + batch.failed_count) / batch.total_emails) * 100
    );
};

// Refresh data
const refreshData = () => {
    isLoading.value = true;
    router.reload({
        only: ["batches"],
        preserveScroll: true,
        onFinish: () => {
            batchesData.value = props.batches.data;
            isLoading.value = false;
        },
    });
};

// Auto-refresh every 5 seconds if needed
// const refreshInterval = setInterval(refreshData, 5000);
// onBeforeUnmount(() => clearInterval(refreshInterval));

// Watch for changes in props
watch(
    () => props.batches,
    (newBatches) => {
        batchesData.value = newBatches.data;
    }
);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Email Batches Monitor" />
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Email Batches</h1>
                    <p class="text-muted-foreground">
                        Monitor all email sending campaigns
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="refreshData"
                    :disabled="isLoading"
                >
                    <RefreshCwIcon
                        class="h-4 w-4 mr-2"
                        :class="{ 'animate-spin': isLoading }"
                    />
                    Refresh
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Active Batches</CardTitle>
                    <CardDescription>
                        Real-time progress of email sending
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Project ID</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Progress</TableHead>
                                <TableHead>Sent</TableHead>
                                <TableHead>Failed</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Started</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="batch in batchesData"
                                :key="batch.id"
                            >
                                <TableCell class="font-medium">
                                    {{ batch.project_id || "N/A" }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :class="`border p-1  ${
                                            statusColors[batch.status]
                                        }`"
                                    >
                                        {{ batch.status || "unknown" }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <Progress
                                            :model-value="
                                                calculateProgress(batch)
                                            "
                                            class="h-2 w-[100px]"
                                        />
                                        <span
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ calculateProgress(batch) }}%
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>{{
                                    batch.sent_count || 0
                                }}</TableCell>
                                <TableCell>{{
                                    batch.failed_count || 0
                                }}</TableCell>
                                <TableCell>{{
                                    batch.total_emails || 0
                                }}</TableCell>
                                <TableCell>
                                    {{
                                        new Date(
                                            batch.created_at
                                        ).toLocaleString()
                                    }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="batchesData.length === 0">
                                <TableCell colspan="7" class="text-center py-4">
                                    No batches found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="batches.links"
                        class="flex items-center justify-end space-x-2 py-4"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!batches.links.prev"
                            @click="router.visit(batches.links.prev)"
                        >
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!batches.links.next"
                            @click="router.visit(batches.links.next)"
                        >
                            Next
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
