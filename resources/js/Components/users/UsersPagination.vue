<script setup>
import { Button } from "@/Components/ui/button";
import { computed } from "vue";
import {
    Pagination,
    PaginationEllipsis,
    PaginationFirst,
    PaginationLast,
    PaginationNext,
    PaginationPrev,
} from "@/Components/ui/pagination";

const props = defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["page-change"]);

const visiblePages = computed(() => {
    const current = props.pagination.currentPage;
    const last = props.pagination.lastPage;
    const delta = 2;

    let range = [];
    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) range.unshift("...");
    if (current + delta < last - 1) range.push("...");

    range.unshift(1);
    if (last !== 1) range.push(last);

    return range;
});
</script>

<template>
    <Pagination>
        <div class="flex items-center gap-1">
            <PaginationFirst
                @click="emit('page-change', 1)"
                :disabled="pagination.currentPage <= 1"
            />
            <PaginationPrev
                @click="emit('page-change', pagination.currentPage - 1)"
                :disabled="pagination.currentPage <= 1"
            />

            <template v-for="(page, index) in visiblePages" :key="index">
                <Button
                    v-if="page === '...'"
                    variant="outline"
                    size="sm"
                    disabled
                >
                    <PaginationEllipsis />
                </Button>
                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    :class="{
                        'bg-primary text-primary-foreground':
                            page === pagination.currentPage,
                    }"
                    @click="emit('page-change', page)"
                >
                    {{ page }}
                </Button>
            </template>

            <PaginationNext
                @click="emit('page-change', pagination.currentPage + 1)"
                :disabled="pagination.currentPage >= pagination.lastPage"
            />
            <PaginationLast
                @click="emit('page-change', pagination.lastPage)"
                :disabled="pagination.currentPage >= pagination.lastPage"
            />
        </div>
        <div class="text-sm text-muted-foreground">
            Showing page {{ pagination.currentPage }} of
            {{ pagination.lastPage }}
        </div>
    </Pagination>
</template>
