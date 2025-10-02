<script setup>
import { ref } from "vue";
import { useDropZone } from "@vueuse/core";
import { useForm } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import {
    Loader2,
    Plus,
    Upload,
    File,
    Trash2,
    AlertCircle,
} from "lucide-vue-next";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { toast } from "vue-sonner";

const isDialogOpen = ref(false);

const form = useForm({
    file: [],
});

function addFiles(files) {
    for (const file of files) {
        if (file.size <= 100 * 1024 * 1024) {
            form.file.push(file);
        } else {
            toast.error(`${file.name} lebih dari 100MB, tidak ditambahkan.`);
        }
    }
}

const dropZoneRef = ref(null);
const fileInputRef = ref(null);

const { isOverDropZone } = useDropZone(dropZoneRef, {
    onDrop(files) {
        addFiles(files);
    },
});

function handleFileInput(e) {
    if (e.target.files) addFiles(e.target.files);
    e.target.value = "";
}

function triggerFileDialog() {
    fileInputRef.value?.click();
}

const removingIndex = ref(null);

const removeFile = (i) => {
    removingIndex.value = i;
    form.file.splice(i, 1);
    removingIndex.value = null;
};

const submit = () => {
    if (form.file.length === 0) {
        toast.error("File tidak boleh kosong");
        return;
    }

    form.post(route("file-manager.store"), {
        showProgress: false,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success("File berhasil di-upload");
            isDialogOpen.value = false;
        },
        onError: () => {
            toast.error("File gagal di-upload");
        },
    });
};
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger as-child>
            <Button variant="outline" class="dark:border-white border-zinc-600">
                <Plus class="mr-2 h-4 w-4" />
                Upload File
            </Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Upload File</DialogTitle>
                <DialogDescription>
                    Drag & drop file Excel/PDF atau klik area untuk pilih. Max
                    100MB.
                </DialogDescription>
            </DialogHeader>

            <template v-if="form.hasErrors">
                <Alert
                    variant="destructive"
                    v-for="(key, index) in form.errors"
                    :key="index"
                >
                    <AlertCircle class="w-4 h-4" />
                    <AlertTitle>Error</AlertTitle>
                    <AlertDescription>
                        {{ key }}
                    </AlertDescription>
                </Alert>
            </template>

            <form
                id="form"
                enctype="multipart/form-data"
                @submit.prevent="submit"
            >
                <!-- Drop zone -->
                <div
                    ref="dropZoneRef"
                    @click="triggerFileDialog"
                    class="mt-2 flex flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 text-center cursor-pointer transition"
                    :class="
                        isOverDropZone
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-zinc-400'
                    "
                >
                    <Upload class="h-8 w-8 text-zinc-500 mb-2" />
                    <p class="text-sm text-zinc-600">
                        Drag & drop file di sini atau klik untuk pilih
                    </p>
                    <input
                        ref="fileInputRef"
                        type="file"
                        multiple
                        accept=".pdf,.xls,.xlsx"
                        class="hidden"
                        @change="handleFileInput"
                    />
                </div>

                <!-- List file -->
                <div v-if="form.file.length" class="mt-4 space-y-2">
                    <div
                        v-for="(file, i) in form.file"
                        :key="i"
                        class="flex items-center justify-between rounded border px-3 py-2 text-sm"
                    >
                        <div class="flex items-center gap-2 truncate">
                            <File class="h-4 w-4 text-zinc-500" />
                            <span class="truncate max-w-[200px]">
                                {{ file.name }}
                            </span>
                            <span class="text-xs text-zinc-500">
                                ({{ (file.size / 1024 / 1024).toFixed(2) }} MB)
                            </span>
                        </div>
                        <Button
                            variant="destructive"
                            size="sm"
                            type="button"
                            @click="removeFile(i)"
                            :disabled="form.processing || removingIndex === i"
                        >
                            <Loader2
                                v-if="removingIndex === i"
                                class="w-4 h-4 animate-spin"
                            />
                            <Trash2 v-else class="w-4 h-4" />
                            Hapus
                        </Button>
                    </div>
                </div>
            </form>

            <DialogFooter class="flex justify-between">
                <DialogClose as-child>
                    <Button type="button" variant="secondary"> Batal </Button>
                </DialogClose>

                <Button
                    type="submit"
                    form="form"
                    :disabled="form.processing"
                    class="disabled:cursor-not-allowed"
                >
                    <Loader2
                        v-if="form.processing"
                        class="w-4 h-4 animate-spin mr-2"
                    />
                    {{ form.processing ? "Loading..." : "Upload" }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
