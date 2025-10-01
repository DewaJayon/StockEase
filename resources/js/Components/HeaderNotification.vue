<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { Bell } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
import { formatRelative } from "@/lib/utils";

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
    DropdownMenuGroup,
} from "@/Components/ui/dropdown-menu";

const notification = ref([]);

const fetchLowStock = async () => {
    try {
        const response = await axios.get(route("low-stock.index"));

        notification.value = response.data.map((item) => ({
            id: item.id,
            name: item.name,
            updated_at: item.updated_at,
            time_ago: formatRelative(item.updated_at),
        }));
    } catch (error) {
        console.error("Gagal ambil notifikasi stok:", error);
    }
};

let intervalId;

onMounted(() => {
    fetchLowStock();
    intervalId = setInterval(fetchLowStock, 30000);
});

onUnmounted(() => {
    clearInterval(intervalId);
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" size="icon" class="relative rounded-full">
                <Bell />

                <span
                    v-if="notification.length"
                    class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold"
                >
                    {{ notification.length }}
                </span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent class="w-80">
            <DropdownMenuLabel class="item-center flex justify-center">
                Notifikasi
            </DropdownMenuLabel>
            <DropdownMenuSeparator v-if="notification.length" />
            <DropdownMenuGroup>
                <template v-if="notification.length">
                    <DropdownMenuItem
                        class="relative cursor-pointer"
                        v-for="(notif, index) in notification"
                        :key="index"
                    >
                        <span
                            class="absolute left-2 top-1/2 -translate-y-1/2 flex h-2 w-2"
                        >
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"
                            />
                            <span
                                class="relative inline-flex rounded-full h-2 w-2 bg-orange-400"
                            />
                        </span>

                        <div class="flex flex-col space-y-1 pl-5">
                            <div class="text-sm font-medium leading-none">
                                Stock product {{ notif.name }} hampir habis
                            </div>
                            <div
                                class="text-xs text-muted-foreground capitalize"
                            >
                                {{ notif.time_ago }}
                            </div>
                        </div>
                    </DropdownMenuItem>
                </template>

                <template v-else>
                    <div
                        class="text-sm text-muted-foreground flex items-center justify-center w-full py-4"
                    >
                        Tidak ada notifikasi
                    </div>
                </template>
            </DropdownMenuGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
