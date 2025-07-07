<script setup>
import { SidebarTrigger } from "@/Components/ui/sidebar";
import { Separator } from "@/Components/ui/separator";
import HeaderNotification from "./HeaderNotification.vue";
import HeaderAccount from "./HeaderAccount.vue";
import HeaderTheme from "./HeaderTheme.vue";
</script>

<template>
    <header class="shadow-sm border-b">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center">
                <SidebarTrigger />
                <Separator orientation="vertical" class="mr-2 h-4" />
                <slot name="breadcrumb" />
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <HeaderTheme />

                    <HeaderNotification />
                </div>
                <div class="relative">
                    <HeaderAccount />
                </div>
            </div>
        </div>
    </header>
</template>
