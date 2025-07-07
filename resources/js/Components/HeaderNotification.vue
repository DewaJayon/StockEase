<script setup>
import { Bell } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
    DropdownMenuGroup,
} from "@/Components/ui/dropdown-menu";
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" size="icon" class="rounded-full">
                <Bell />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-80">
            <DropdownMenuLabel class="item-center flex justify-center">
                Notifikasi
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem class="relative cursor-pointer">
                    <span
                        class="absolute left-2 top-1/2 -translate-y-1/2 flex h-2 w-2"
                    >
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"
                        ></span>
                        <span
                            class="relative inline-flex rounded-full h-2 w-2 bg-orange-400"
                        ></span>
                    </span>

                    <div class="flex flex-col space-y-1 pl-5">
                        <div class="text-sm font-medium leading-none">
                            Stock product a hampir habis
                        </div>
                        <div class="text-xs text-muted-foreground">
                            2 hari yang lalu
                        </div>
                    </div>
                </DropdownMenuItem>
            </DropdownMenuGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
