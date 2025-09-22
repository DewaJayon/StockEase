<script setup>
import { Badge } from "@/Components/ui/badge";
import FilePdfIcon from "@/Components/FilePdfIcon.vue";
import FileExcelIcon from "@/Components/FileExcelIcon.vue";
import axios from "axios";
import { toast } from "vue-sonner";
import { Download, EllipsisVertical, Loader2, Trash2 } from "lucide-vue-next";
import { Card, CardContent } from "@/Components/ui/card";
import { ref } from "vue";

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";

import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/Components/ui/tooltip";

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/Components/ui/alert-dialog";

const props = defineProps({
    file: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["delete", "download"]);

const isLoading = ref(false);
const isDialogOpen = ref(false);

const handleDownload = (filePath) => {
    window.open(route("file-manager.download", filePath), "_blank");
};

const handleDelete = async (filePath) => {
    isLoading.value = true;
    isDialogOpen.value = true;

    await axios
        .delete(route("file-manager.destroy", filePath))
        .then((response) => {
            emit("delete");
            toast.success(response.data.message);
            isLoading.value = false;
            isDialogOpen.value = false;
        })
        .catch((error) => {
            console.log(error);
            toast.error("File gagal dihapus");
        })
        .finally(() => {
            isLoading.value = false;
            isDialogOpen.value = false;
        });
};
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
                        <FileExcelIcon
                            class="w-6 h-6"
                            v-if="file.file_extension == 'xlsx'"
                        />

                        <FilePdfIcon
                            class="w-6 h-6"
                            v-if="file.file_extension == 'pdf'"
                        />
                    </div>
                    <div className="min-w-0 flex-1">
                        <div
                            className="flex items-center justify-between gap-2"
                        >
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <h3
                                            class="truncate text-sm font-semibold block max-w-full"
                                        >
                                            {{ file.name }}
                                        </h3>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{{ file.name }}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>

                            <div class="flex items-center gap-2">
                                <Badge class="uppercase">
                                    {{ file.file_extension }}
                                </Badge>
                                <DropdownMenu>
                                    <DropdownMenuTrigger>
                                        <EllipsisVertical class="w-4 h-4" />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuItem
                                            class="cursor-pointer"
                                            @click="handleDownload(file.path)"
                                        >
                                            <Download class="w-4 h-4 mr-2" />
                                            Download
                                        </DropdownMenuItem>

                                        <DropdownMenuItem
                                            class="cursor-pointer text-red-500"
                                            @click="isDialogOpen = true"
                                        >
                                            <Trash2 class="w-4 h-4 mr-2" />
                                            Hapus
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>

                                <AlertDialog v-model:open="isDialogOpen">
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>
                                                Apakah anda yakin ingin
                                                menghapus?
                                            </AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Data yang telah dihapus tidak
                                                dapat dikembalikan! Tindakan ini
                                                tidak dapat dibatalkan!
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel
                                                >Batal</AlertDialogCancel
                                            >
                                            <AlertDialogAction
                                                @click="handleDelete(file.path)"
                                                class="bg-red-500 hover:bg-red-600 text-white"
                                            >
                                                <Loader2
                                                    v-if="isLoading"
                                                    class="w-4 h-4 animate-spin"
                                                />
                                                {{
                                                    isLoading
                                                        ? "Loading..."
                                                        : "Hapus"
                                                }}
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </div>
                        <div
                            className="mt-1 flex flex-wrap items-center gap-2 text-xs "
                        >
                            <span> {{ file.size }} </span>
                            <span
                                className="h-1 w-1 rounded-full bg-gray-300"
                                aria-hidden
                            />

                            <span> Dibuat {{ file.last_modified }} </span>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
