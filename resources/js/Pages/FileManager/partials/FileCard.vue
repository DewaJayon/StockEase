<script setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";

import {
    Download,
    EllipsisVertical,
    FileSpreadsheet,
    Trash2,
} from "lucide-vue-next";

import { Card, CardContent } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
</script>

<template>
    <Card>
        <CardContent class="p-0">
            <div
                className="group relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm transition hover:shadow-md"
            >
                <div className="flex items-start gap-3">
                    <div
                        className="shrink-0 rounded-lg p-2 ring-1 ring-gray-100 dark:ring-gray-800"
                    >
                        <FileSpreadsheet />
                    </div>
                    <div className="min-w-0 flex-1">
                        <div
                            className="flex items-center justify-between gap-2"
                        >
                            <h3 className="truncate text-sm font-semibold">
                                Lorem ipsum
                            </h3>
                            <div class="flex items-center gap-2">
                                <Badge>Excel</Badge>
                                <DropdownMenu>
                                    <DropdownMenuTrigger>
                                        <EllipsisVertical class="w-4 h-4" />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuItem
                                            class="cursor-pointer"
                                        >
                                            <Download />
                                            Download
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            class="cursor-pointer"
                                        >
                                            <Trash2 />
                                            Hapus
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                        <div
                            className="mt-1 flex flex-wrap items-center gap-2 text-xs "
                        >
                            <span> 10MB </span>
                            <span
                                className="h-1 w-1 rounded-full bg-gray-300"
                                aria-hidden
                            />

                            <span> Dibuat 2022-01-01 </span>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
