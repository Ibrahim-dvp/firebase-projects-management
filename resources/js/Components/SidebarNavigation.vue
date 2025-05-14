<script setup>
import { Link } from "@inertiajs/vue3";
import {
    NavigationMenu,
    NavigationMenuList,
    NavigationMenuItem,
    NavigationMenuLink,
} from "@/Components/ui/navigation-menu";
import { Separator } from "@/Components/ui/separator";
import { Switch } from "@/Components/ui/switch";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Button } from "@/Components/ui/button";

import {
    HomeIcon,
    UsersIcon,
    SettingsIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    MoonIcon,
    SunIcon,
    Folder,
    LogOutIcon,
    Upload,
    Mail,
    UserCog,
    UserCog2,
} from "lucide-vue-next";

const props = defineProps({
    isCollapsed: Boolean,
    isDark: Boolean,
    user: Object,
});

const emit = defineEmits(["toggleDarkMode", "toggleSidebar"]);

const navItems = [
    { name: "Dashboard", href: "dashboard", icon: HomeIcon },
    {
        name: "Projects",
        href: "projects.index",
        icon: Folder,
    },
    { name: "Users", href: "users.index", icon: UsersIcon },
    { name: "Uploads", href: "uploads.index", icon: Upload },
    { name: "Emails", href: "emails-monitor.index", icon: Mail },
];
</script>

<template>
    <div class="flex h-full flex-col">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between p-3 border-b h-14">
            <h1
                class="text-lg font-semibold truncate transition-all"
                :class="{
                    'opacity-0 w-0': isCollapsed,
                    'opacity-100 w-auto': !isCollapsed,
                }"
            >
                Zmachine
            </h1>
            <button
                @click="emit('toggleSidebar')"
                class="rounded-md p-1 hover:bg-accent transition-colors focus:outline-none"
                aria-label="Toggle sidebar"
            >
                <ChevronLeftIcon v-if="!isCollapsed" class="h-4 w-4" />
                <ChevronRightIcon v-else class="h-4 w-4" />
            </button>
        </div>

        <!-- Navigation -->
        <NavigationMenu orientation="vertical" class="flex-1 overflow-y-auto">
            <NavigationMenuList class="flex-col items-stretch gap-0.5 p-2">
                <NavigationMenuItem v-for="item in navItems" :key="item.name">
                    <NavigationMenuLink as-child>
                        <Link
                            :href="route(item.href)"
                            class="flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground w-full"
                            :class="{
                                'bg-accent': route().current(item.href),
                                'pl-2.5': isCollapsed,
                            }"
                        >
                            <component
                                :is="item.icon"
                                class="h-4 w-4 flex-shrink-0"
                                :class="{ 'mx-auto': isCollapsed }"
                            />
                            <span
                                v-if="!isCollapsed"
                                class="truncate text-left"
                            >
                                {{ item.name }}
                            </span>
                        </Link>
                    </NavigationMenuLink>
                </NavigationMenuItem>
            </NavigationMenuList>
        </NavigationMenu>

        <!-- Dark Mode Toggle -->
        <div
            class="flex items-center justify-center gap-2 rounded-md px-2 py-1.5 hover:bg-accent transition-colors"
            :class="{ 'justify-center': isCollapsed }"
        >
            <template v-if="!isCollapsed">
                <SunIcon class="h-4 w-4 flex-shrink-0" />
                <Switch
                    :checked="isDark"
                    @click="emit('toggleDarkMode')"
                    class="scale-75"
                />
                <MoonIcon class="h-4 w-4 flex-shrink-0" />
            </template>
            <Switch
                v-else
                :checked="isDark"
                @click="emit('toggleDarkMode')"
                class="scale-75 mx-auto"
            />
        </div>

        <!-- Sidebar Footer -->
        <div class="p-2 border-t space-y-2">
            <!-- Profile Link -->
            <Link
                :href="route('profile.edit')"
                class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-accent hover:text-accent-foreground w-full"
                :class="{ 'justify-center': isCollapsed }"
            >
                <Avatar class="h-7 w-7 flex-shrink-0 bg-gray-700">
                    <AvatarFallback class="text-white">{{
                        user.name.charAt(0)
                    }}</AvatarFallback>
                </Avatar>
                <div v-if="!isCollapsed" class="flex-1 min-w-0">
                    <p class="truncate font-medium">{{ user.name }}</p>
                    <p class="text-xs text-muted-foreground truncate">
                        View profile
                    </p>
                </div>
            </Link>

            <Link
                v-if="user.role === 'admin'"
                :href="route('admin.index')"
                class="flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground w-full"
                :class="{
                    'bg-accent': route().current('admin.index'),
                    'pl-2.5': isCollapsed,
                }"
            >
                <UserCog
                    class="h-4 w-4 flex-shrink-0"
                    :class="{ 'mx-auto': isCollapsed }"
                />
                <span v-if="!isCollapsed" class="truncate text-left">
                    Admin
                </span>
            </Link>

            <!-- Logout Button -->
            <form
                @submit.prevent="$inertia.post(route('logout'))"
                class="w-full"
            >
                <Button
                    type="submit"
                    variant="ghost"
                    class="w-full justify-start gap-2 px-2 py-1.5 h-auto text-sm hover:bg-accent hover:text-accent-foreground"
                    :class="{ 'justify-center': isCollapsed }"
                >
                    <LogOutIcon class="h-4 w-4 flex-shrink-0" />
                    <span v-if="!isCollapsed">Sign out</span>
                </Button>
            </form>
        </div>
    </div>
</template>
