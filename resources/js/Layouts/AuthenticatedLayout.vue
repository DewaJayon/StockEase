<script setup>
import { Head } from "@inertiajs/vue3";
import AppSidebar from "@/Components/AppSidebar.vue";
import { SidebarProvider } from "@/Components/ui/sidebar";
import Header from "@/Components/Header.vue";
</script>

<template>
    <div class="flex h-screen">
        <SidebarProvider>
            <AppSidebar />
            <div class="flex-1 flex flex-col overflow-hidden">
                <Header>
                    <template #breadcrumb>
                        <slot name="breadcrumb" />
                    </template>
                </Header>
                <main class="flex-1 overflow-x-hidden overflow-y-auto pt-2">
                    <slot />
                </main>
            </div>
        </SidebarProvider>
    </div>
</template>
