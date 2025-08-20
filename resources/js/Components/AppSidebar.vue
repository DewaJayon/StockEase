<script setup>
import { Link } from "@inertiajs/vue3";

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

import {
    PackageSearch,
    Tag,
    Truck,
    LayoutDashboard,
    ChevronDown,
    UserRound,
    ShoppingCart,
    ShoppingBag,
    PackageCheck,
    CircleDollarSign,
    FileText,
    FileBox,
} from "lucide-vue-next";

import {
    CollapsibleContent,
    CollapsibleRoot,
    CollapsibleTrigger,
} from "reka-ui";

const general = [
    {
        title: "Dashboard",
        routeName: "home",
        icon: LayoutDashboard,
    },
    {
        title: "User",
        routeName: "users.index",
        icon: UserRound,
    },
    {
        title: "POS (Kasir)",
        routeName: "pos.index",
        icon: ShoppingCart,
    },
];

const manageData = [
    {
        title: "Produk",
        routeName: "product.index",
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

const transaction = [
    {
        title: "Pembelian",
        routeName: "purcase.index",
        icon: PackageCheck,
    },
    {
        title: "Penjualan",
        routeName: "sale.index",
        icon: ShoppingBag,
    },
    {
        title: "Transaksi Midtrans",
        routeName: "midtrans.index",
        icon: CircleDollarSign,
    },
];

const reports = [
    {
        title: "Laporan Penjualan",
        routeName: "reports.sale.index",
        icon: FileText,
    },
    {
        title: "Laporan Pembelian",
        routeName: "reports.purchase.index",
        icon: FileBox,
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
                        <SidebarMenuItem v-for="item in general">
                            <SidebarMenuButton
                                asChild
                                :is-active="route().current(item.routeName)"
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
                                            item.title === 'Produk'
                                                ? route().current('product.*')
                                                : route().current(
                                                      item.routeName
                                                  )
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

            <CollapsibleRoot defaultOpen class="group/collapsible">
                <SidebarGroup>
                    <SidebarGroupLabel asChild>
                        <CollapsibleTrigger>
                            Data Transaksi dan Penjualan
                            <ChevronDown
                                class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-180"
                            />
                        </CollapsibleTrigger>
                    </SidebarGroupLabel>
                    <CollapsibleContent>
                        <SidebarGroupContent>
                            <SidebarMenu>
                                <SidebarMenuItem
                                    v-for="item in transaction"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        asChild
                                        :is-active="
                                            item.title === 'Penjualan'
                                                ? route().current('sale.*')
                                                : route().current(
                                                      item.routeName
                                                  )
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

            <CollapsibleRoot defaultOpen class="group/collapsible">
                <SidebarGroup>
                    <SidebarGroupLabel asChild>
                        <CollapsibleTrigger>
                            Laporan
                            <ChevronDown
                                class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-180"
                            />
                        </CollapsibleTrigger>
                    </SidebarGroupLabel>
                    <CollapsibleContent>
                        <SidebarGroupContent>
                            <SidebarMenu>
                                <SidebarMenuItem
                                    v-for="item in reports"
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
