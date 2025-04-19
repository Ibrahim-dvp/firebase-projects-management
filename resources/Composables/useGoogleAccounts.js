import { ref } from "vue";
import { router } from "@inertiajs/vue3";

export function useGoogleAccounts() {
    const loading = ref(false);
    const error = ref(null);

    const deleteAccount = (accountId) => {
        if (confirm("Are you sure you want to delete this account?")) {
            loading.value = true;
            router.delete(`/accounts/${accountId}`, {
                onSuccess: () => {
                    // Handle success
                },
                onError: (err) => {
                    error.value = err.message;
                },
                onFinish: () => {
                    loading.value = false;
                },
            });
        }
    };

    return {
        loading,
        error,
        deleteAccount,
    };
}
