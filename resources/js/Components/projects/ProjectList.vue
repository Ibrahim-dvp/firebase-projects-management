<script setup>
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
    CardFooter,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import {
    CheckCircleIcon,
    CuboidIcon,
    DatabaseIcon,
    Server,
    GlobeIcon,
    CalendarIcon,
} from "lucide-vue-next";

defineProps({
    project: {
        type: Object,
        required: true,
    },
});

// Format creation date (mock - replace with actual date from API if available)
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>
<template>
    <Card class="hover:shadow-md transition-shadow">
        <CardHeader class="pb-3">
            <div class="flex justify-between items-start">
                <div>
                    <CardTitle class="flex items-center gap-2">
                        <CuboidIcon class="w-5 h-5 text-primary" />
                        <span class="truncate">{{
                            project.displayName || "Unnamed Project"
                        }}</span>
                    </CardTitle>
                    <CardDescription class="mt-1 truncate">
                        ID: {{ project.projectId }}
                    </CardDescription>
                </div>
                <Badge
                    variant="outline"
                    class="flex items-center"
                    :class="{
                        'text-green-600 dark:text-green-400':
                            project.state === 'ACTIVE',
                        'text-yellow-600 dark:text-yellow-400':
                            project.state === 'PENDING',
                        'text-red-600 dark:text-red-400':
                            project.state === 'DELETED',
                    }"
                >
                    <CheckCircleIcon class="w-3 h-3 mr-1" />
                    {{ project.state || "ACTIVE" }}
                </Badge>
            </div>
        </CardHeader>
        <CardContent>
            <div class="grid gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <p class="text-xs text-muted-foreground">
                            Project Number
                        </p>
                        <p class="text-sm font-mono">
                            {{ project.projectNumber }}
                        </p>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-muted-foreground">Created</p>
                        <p class="text-sm flex items-center gap-1">
                            <CalendarIcon class="w-3.5 h-3.5" />
                            {{ formatDate(new Date()) }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground mb-2">Services</p>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-if="project.resources?.hostingSite"
                            variant="outline"
                            class="flex items-center gap-1"
                        >
                            <GlobeIcon class="w-3 h-3" />
                            Hosting
                        </Badge>
                        <Badge
                            v-if="project.resources?.realtimeDatabaseInstance"
                            variant="outline"
                            class="flex items-center gap-1"
                        >
                            <DatabaseIcon class="w-3 h-3" />
                            Database
                        </Badge>
                        <Badge
                            v-if="project.resources?.storageBucket"
                            variant="outline"
                            class="flex items-center gap-1"
                        >
                            <Server class="w-3 h-3" />
                            Storage
                        </Badge>
                    </div>
                </div>
            </div>
        </CardContent>
        <CardFooter class="border-t pt-3">
            <Button variant="outline" size="sm" class="w-full">
                View project details
            </Button>
        </CardFooter>
    </Card>
</template>
