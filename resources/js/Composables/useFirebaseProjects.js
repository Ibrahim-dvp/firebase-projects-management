import { ref } from "vue";
import axios from "axios";

export default function useFirebaseProjects() {
    const firebaseProjects = ref([]);
    const isLoading = ref(false);
    const error = ref(null);

    // Fetch all projects
    const fetchProjects = async (googleAccounts) => {
        isLoading.value = true;
        error.value = null;
        firebaseProjects.value = [];

        try {
            const projectPromises = googleAccounts.map(async (account) => {
                const response = await axios.get(
                    "https://firebase.googleapis.com/v1beta1/projects",
                    {
                        headers: {
                            Authorization: `Bearer ${account.access_token}`,
                        },
                    }
                );
                return (response.data.results || []).map((project) => ({
                    ...project,
                    accountEmail: account.email,
                }));
            });

            const allProjects = await Promise.all(projectPromises);
            firebaseProjects.value = allProjects.flat();
        } catch (err) {
            error.value =
                err.response?.data?.error?.message ||
                "Failed to fetch projects";
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    // Create new Firebase project
    const createProject = async (account, { projectId, displayName }) => {
        try {
            const createResponse = await axios.post(
                "https://cloudresourcemanager.googleapis.com/v1/projects",
                { projectId: projectId, name: displayName },
                { headers: { Authorization: `Bearer ${account.access_token}` } }
            );
            // 2. Wait for provisioning
            await new Promise((resolve) => setTimeout(resolve, 30000));

            // 3. Add Firebase services
            const response = await axios.post(
                `https://firebase.googleapis.com/v1beta1/projects/${projectId}:addFirebase`,
                {},
                {
                    headers: {
                        Authorization: `Bearer ${account.access_token}`,
                        "Content-Type": "application/json",
                    },
                }
            );

            return { success: true, data: response.data };
        } catch (err) {
            console.error("Project creation error:", err);
            return {
                success: false,
                error: {
                    message:
                        err.response?.data?.error?.message ||
                        "Failed to create project",
                    details: err.response?.data,
                },
            };
        }
    };

    // backup code for createProject
    // const createProject = async (account, { projectId, displayName }) => {
    //     try {
    //         // 1. Create Google Cloud Project
    //         const createResponse = await axios.post(
    //             "https://cloudresourcemanager.googleapis.com/v1/projects",
    //             { projectId: projectId, name: displayName },
    //             {
    //                 headers: {
    //                     Authorization: `Bearer ${account.access_token}`,
    //                     "Content-Type": "application/json"
    //                 }
    //             }
    //         );

    //         // 2. Improved waiting mechanism
    //         let attempts = 0;
    //         let lastError = null;

    //         while (attempts < 6) { // Try for max 3 minutes (6 attempts × 30 seconds)
    //             try {
    //                 // 3. Add Firebase services
    //                 const response = await axios.post(
    //                     `https://firebase.googleapis.com/v1beta1/projects/${projectId}:addFirebase`,
    //                     {},
    //                     {
    //                         headers: {
    //                             Authorization: `Bearer ${account.access_token}`,
    //                             "Content-Type": "application/json",
    //                         },
    //                     }
    //                 );

    //                 return { success: true, data: response.data };
    //             } catch (err) {
    //                 lastError = err;
    //                 if (err.response?.status === 403 && attempts < 5) {
    //                     // If forbidden but we have retries left, wait and try again
    //                     await new Promise(resolve => setTimeout(resolve, 30000));
    //                     attempts++;
    //                     continue;
    //                 }
    //                 throw err; // Re-throw if not a 403 or no retries left
    //             }
    //         }

    //     } catch (err) {
    //         console.error("Project creation error:", err);
    //         return {
    //             success: false,
    //             error: {
    //                 message: err.response?.data?.error?.message || "Failed to create project",
    //                 details: err.response?.data,
    //                 status: err.response?.status
    //             },
    //         };
    //     }
    // };

    return {
        firebaseProjects,
        isLoading,
        error,

        fetchProjects,
        createProject,
    };
}
