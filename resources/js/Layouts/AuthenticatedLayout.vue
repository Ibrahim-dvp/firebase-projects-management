<script setup>
import { ref, onMounted } from "vue";
import SidebarNavigation from "@/Components/SidebarNavigation.vue";
import { Toaster } from "@/Components/ui/toast";

// Dark mode state
const isDark = ref(false);
const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle("dark", isDark.value);
    localStorage.setItem("darkMode", isDark.value);
};

// Collapsible sidebar state
const isCollapsed = ref(false);
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem("sidebarCollapsed", isCollapsed.value);
};

// Initialize from localStorage
onMounted(() => {
    isDark.value = localStorage.getItem("darkMode") === "true";
    isCollapsed.value = localStorage.getItem("sidebarCollapsed") === "true";
    document.documentElement.classList.toggle("dark", isDark.value);
});
</script>

<template>
    <Toaster />
    <div class="flex h-screen overflow-hidden bg-background text-foreground">
        <!-- Fixed Sidebar - Narrower Width -->
        <aside
            class="border-r bg-background flex flex-col h-full fixed z-50 transition-all duration-300 ease-in-out"
            :class="{
                'w-56': !isCollapsed, // Reduced from 64 to 56
                'w-14': isCollapsed, // Reduced from 16 to 14
            }"
        >
            <SidebarNavigation
                :is-collapsed="isCollapsed"
                :is-dark="isDark"
                :user="$page.props.auth.user"
                @toggle-dark-mode="toggleDarkMode"
                @toggle-sidebar="toggleSidebar"
            />
        </aside>

        <!-- Main Content -->
        <div
            class="flex-1 flex flex-col h-full overflow-auto transition-all duration-300 ease-in-out"
            :class="{
                'ml-56': !isCollapsed, // Adjusted to match sidebar
                'ml-14': isCollapsed, // Adjusted to match sidebar
            }"
        >
            <main class="flex-1 overflow-auto p-4 md:p-6">
                <div class="max-w-7xl mx-auto">
                    <!-- Centering container -->
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
