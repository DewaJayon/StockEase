<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { filterMenuByRole } from "@/lib/utils";

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
    Warehouse,
    Logs,
    File,
    Scale,
    CalendarClock,
} from "lucide-vue-next";

import {
    CollapsibleContent,
    CollapsibleRoot,
    CollapsibleTrigger,
} from "reka-ui";

const user = usePage().props.auth.user;

// Daftar menu dengan roles
const general = [
    {
        title: "Dashboard",
        routeName: "dashboard",
        icon: LayoutDashboard,
        roles: ["admin", "cashier", "warehouse"],
    },
    {
        title: "User",
        routeName: "users.index",
        icon: UserRound,
        roles: ["admin"],
    },
    {
        title: "POS (Kasir)",
        routeName: "pos.index",
        icon: ShoppingCart,
        roles: ["admin", "cashier"],
    },
];

const manageData = [
    {
        title: "Produk",
        routeName: "product.index",
        icon: PackageSearch,
        roles: ["admin", "warehouse"],
    },
    {
        title: "Kategori",
        routeName: "category.index",
        icon: Tag,
        roles: ["admin"],
    },
    {
        title: "Satuan",
        routeName: "unit.index",
        icon: Scale,
        roles: ["admin", "warehouse"],
    },
    {
        title: "Supplier",
        routeName: "supplier.index",
        icon: Truck,
        roles: ["admin", "warehouse"],
    },
];

const transaction = [
    {
        title: "Pembelian",
        routeName: "purchase.index",
        icon: PackageCheck,
        roles: ["admin", "warehouse"],
    },
    {
        title: "Penjualan",
        routeName: "sale.index",
        icon: ShoppingBag,
        roles: ["admin", "cashier"],
    },
    {
        title: "Transaksi Midtrans",
        routeName: "midtrans.index",
        icon: CircleDollarSign,
        roles: ["admin", "cashier"],
    },
];

const reports = [
    {
        title: "Laporan Penjualan",
        routeName: "reports.sale.index",
        icon: FileText,
        roles: ["admin", "cashier"],
    },
    {
        title: "Laporan Pembelian",
        routeName: "reports.purchase.index",
        icon: FileBox,
        roles: ["admin", "warehouse"],
    },
    {
        title: "Laporan Stock",
        routeName: "reports.stock.index",
        icon: Warehouse,
        roles: ["admin", "warehouse"],
    },
    {
        title: "Laporan Kedaluwarsa",
        routeName: "reports.expiry.index",
        icon: CalendarClock,
        roles: ["admin", "warehouse"],
    },
];

const other = [
    {
        title: "Stock Opname",
        routeName: "stock-adjustment.index",
        icon: Scale,
        roles: ["admin", "warehouse"],
    },
    {
        title: "Log Stock",
        routeName: "log-stock.index",
        icon: Logs,
        roles: ["admin", "warehouse"],
    },
    {
        title: "File Manager",
        routeName: "file-manager.index",
        icon: File,
        roles: ["admin", "cashier", "warehouse"],
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
            <!-- General -->
            <SidebarGroup>
                <SidebarGroupLabel> Dashboard </SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in filterMenuByRole(general, user.role)"
                            :key="item.title"
                        >
                            <SidebarMenuButton
                                as-child
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

            <!-- Manajemen Data -->
            <CollapsibleRoot
                v-if="filterMenuByRole(manageData, user.role).length"
                default-open
                class="group/collapsible"
            >
                <SidebarGroup>
                    <SidebarGroupLabel as-child>
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
                                    v-for="item in filterMenuByRole(
                                        manageData,
                                        user.role,
                                    )"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        as-child
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

            <!-- Transaction -->
            <CollapsibleRoot default-open class="group/collapsible">
                <SidebarGroup>
                    <SidebarGroupLabel as-child>
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
                                    v-for="item in filterMenuByRole(
                                        transaction,
                                        user.role,
                                    )"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        as-child
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

            <!-- Reports -->
            <CollapsibleRoot default-open class="group/collapsible">
                <SidebarGroup>
                    <SidebarGroupLabel as-child>
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
                                    v-for="item in filterMenuByRole(
                                        reports,
                                        user.role,
                                    )"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        as-child
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

            <!-- Other -->
            <CollapsibleRoot default-open class="group/collapsible">
                <SidebarGroup>
                    <SidebarGroupLabel as-child>
                        <CollapsibleTrigger>
                            Lainnya
                            <ChevronDown
                                class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-180"
                            />
                        </CollapsibleTrigger>
                    </SidebarGroupLabel>
                    <CollapsibleContent>
                        <SidebarGroupContent>
                            <SidebarMenu>
                                <SidebarMenuItem
                                    v-for="item in filterMenuByRole(
                                        other,
                                        user.role,
                                    )"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        as-child
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
