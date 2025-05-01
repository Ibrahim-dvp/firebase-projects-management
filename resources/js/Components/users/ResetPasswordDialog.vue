<script setup>
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/Components/ui/dialog";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { useToast } from "@/Components/ui/toast/use-toast";

const props = defineProps({
    modelValue: Boolean, // v-model:open
    email: String,
    uid: String,
    project: String,
});
const emit = defineEmits(["update:modelValue"]);

const form = useForm({
    uid: props.uid,
    email: props.email,
    password: "",
    password_confirmation: "",
    project: props.project,
});

// keep form.email & form.project in sync
watch(
    () => props.uid,
    (v) => (form.uid = v)
);

watch(
    () => props.email,
    (v) => (form.email = v)
);
watch(
    () => props.project,
    (v) => (form.project = v)
);

function close() {
    emit("update:modelValue", false);
    form.reset("password", "password_confirmation");
}

function submit() {
    form.post(route("users.resetPasswordAdmin"), {
        preserveState: true,
        onSuccess: () => {
            const { toast } = useToast();
            toast({
                title: "Password Updated",
                description: `New password set for ${form.email}`,
            });
            close();
        },
        onError: (error) => {
            const { toast } = useToast();
            toast({
                title: "Error",
                description: error,
            });
            // close();
        },
    });
}
</script>

<template>
    <Dialog
        :open="modelValue"
        @openChange="(open) => emit('update:modelValue', open)"
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Reset Password</DialogTitle>
            </DialogHeader>
            <DialogDescription class="mt-1">
                Set a new password for <strong>{{ form.email }}</strong>
            </DialogDescription>

            <form @submit.prevent="submit" class="space-y-4 mt-4">
                <input type="hidden" v-model="form.uid" />
                <input type="hidden" v-model="form.project" />
                <div>
                    <Label for="password">New Password</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        autocomplete="new-password"
                    />
                    <p v-if="form.errors.password" class="text-sm text-red-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div>
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                    />
                    <p
                        v-if="form.errors.password_confirmation"
                        class="text-sm text-red-600"
                    >
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <DialogFooter class="flex justify-end space-x-2">
                    <Button variant="ghost" type="button" @click="close">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Reset Password
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
