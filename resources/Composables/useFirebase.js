import { ref } from "vue";
import axios from "axios";

export function useFirebase() {
    const projects = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const fetchProjects = async (token) => {
        if (!token) return;

        loading.value = true;
        error.value = null;
        projects.value = [];

        try {
            const response = await axios.get(
                `https://firebase.googleapis.com/v1beta1/projects`,
                {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                }
            );
            projects.value = response.data.results || [];
        } catch (err) {
            error.value =
                err.response?.data?.error?.message ||
                "Failed to fetch projects";
        } finally {
            loading.value = false;
        }
    };

    return {
        projects,
        loading,
        error,
        fetchProjects,
    };
}
