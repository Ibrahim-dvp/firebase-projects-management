// import { ref } from "vue";
// import axios from "axios";

// export default function useFirebaseProjects(googleAccounts) {
//     const firebaseProjects = ref([]);
//     const isLoading = ref(false);
//     const error = ref(null);
//     console.log(googleAccounts);

//     const fetchProjects = async () => {
//         isLoading.value = true;
//         error.value = null;
//         firebaseProjects.value = [];

//         try {
//             const projectPromises = googleAccounts.map(async (account) => {
//                 const response = await axios.get(
//                     "https://firebase.googleapis.com/v1beta1/projects",
//                     {
//                         headers: {
//                             Authorization: `Bearer ${account.access_token}`,
//                         },
//                     }
//                 );
//                 return (response.data.results || []).map((project) => ({
//                     ...project,
//                     accountEmail: account.email,
//                 }));
//             });

//             const allProjects = await Promise.all(projectPromises);
//             firebaseProjects.value = allProjects.flat();
//         } catch (err) {
//             error.value =
//                 err.response?.data?.error?.message ||
//                 "Failed to fetch projects";
//             throw err; // Re-throw for component to handle if needed
//         } finally {
//             isLoading.value = false;
//         }
//     };

//     return {
//         firebaseProjects,
//         isLoading,
//         error,
//         fetchProjects,
//     };
// }

// resources/js/Composables/useFirebaseProjects.js
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
            // const createResponse = await axios.post(
            //     "https://cloudresourcemanager.googleapis.com/v1/projects",
            //     { projectId, name: displayName },
            //     { headers: { Authorization: `Bearer ${account.access_token}` } }
            // );
            // 2. Wait for provisioning
            // await new Promise((resolve) => setTimeout(resolve, 5000));

            // 3. Add Firebase services
            const createResponse = await axios.post(
                `https://firebase.googleapis.com/v1beta1/projects/`,
                { projectId, name: displayName },
                {
                    headers: {
                        Authorization: `Bearer ${account.access_token}`,
                        "Content-Type": "application/json",
                    },
                }
            );
            console.log("Project creation response:", createResponse.data);
            return;

            return { success: true, data: createResponse.data };
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

    return {
        firebaseProjects,
        isLoading,
        error,

        fetchProjects,
        createProject,
    };
}
