<script setup>
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
} from "@/Components/ui/sidebar";
import { Link } from "@inertiajs/vue3";
import {
    PackageSearch,
    Tag,
    Truck,
    LayoutDashboard,
    ChevronDown,
} from "lucide-vue-next";
import {
    CollapsibleContent,
    CollapsibleRoot,
    CollapsibleTrigger,
} from "reka-ui";

const manageData = [
    {
        title: "Produk",
        routeName: "#",
        icon: PackageSearch,
    },
    {
        title: "Kategori",
        routeName: "category.index",
        icon: Tag,
    },
    {
        title: "Supplier",
        routeName: "supplier.index",
        icon: Truck,
    },
];
</script>

<template>
    <Sidebar>
        <SidebarHeader>
            <div class="flex items-center gap-2 justify-center mt-2">
                <img
                    class="h-8 w-8"
                    src="/img/StockEase-Logo.png"
                    alt="Stock Ease"
                />
                <span class="font-bold dark:text-white">Stock Ease</span>
            </div>
        </SidebarHeader>
        <SidebarContent>
            <SidebarGroup>
                <SidebarGroupLabel> Dashboard </SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                asChild
                                :is-active="route().current('home')"
                            >
                                <Link :href="route('home')">
                                    <LayoutDashboard />
                                    Dashboard
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <CollapsibleRoot defaultOpen class="group/collapsible">
                <SidebarGroup>
                    <SidebarGroupLabel asChild>
                        <CollapsibleTrigger>
                            Manajemen Data
                            <ChevronDown
                                class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-180"
                            />
                        </CollapsibleTrigger>
                    </SidebarGroupLabel>
                    <CollapsibleContent>
                        <SidebarGroupContent>
                            <SidebarMenu>
                                <SidebarMenuItem
                                    v-for="item in manageData"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        asChild
                                        :is-active="
                                            route().current(item.routeName)
                                        "
                                    >
                                        <Link
                                            :href="
                                                route().has(item.routeName)
                                                    ? route(item.routeName)
                                                    : '#'
                                            "
                                        >
                                            <component :is="item.icon" />
                                            <span>{{ item.title }}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarGroupContent>
                    </CollapsibleContent>
                </SidebarGroup>
            </CollapsibleRoot>
        </SidebarContent>
        <SidebarFooter />
    </Sidebar>
</template>
