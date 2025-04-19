<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>
<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Users
            </h2>
        </template>
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">Users</h1>
            <p>Welcome to the Users page!</p>
        </div>
    </AuthenticatedLayout>
</template>
