import { computed } from "vue";

export default function useProjectFilters(
    projects,
    selectedAccountEmail,
    searchQuery
) {
    const filteredProjects = computed(() => {
        if (!projects.value.length) return [];

        return projects.value.filter((project) => {
            // Account filter
            const accountMatch =
                selectedAccountEmail.value === "all" ||
                project.accountEmail === selectedAccountEmail.value;

            // Search filter
            const searchMatch =
                !searchQuery.value ||
                (project.displayName || "")
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase()) ||
                (project.projectId || "")
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase());

            return accountMatch && searchMatch;
        });
    });

    return {
        filteredProjects,
    };
}
