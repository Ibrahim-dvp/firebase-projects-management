<script setup>
import { CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { PlusIcon } from "lucide-vue-next";

const defineEmits = defineEmits(["linkGoogleAccount"]);
</script>
<template>
    <CardHeader class="flex items-center flex-row justify-between">
        <CardTitle class="text-lg"> Connected Accounts </CardTitle>
        <Button @click="$emit('linkGoogleAccount')" class="gap-2">
            <PlusIcon class="h-4 w-4" />
            Add Acount
        </Button>
    </CardHeader>
</template>
