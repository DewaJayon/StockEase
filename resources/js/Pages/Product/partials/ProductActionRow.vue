<script setup>
import { Button } from "@/Components/ui/button";
import { Eye, Loader2, Pencil, Trash2 } from "lucide-vue-next";
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/Components/ui/alert-dialog";
import { toast } from "vue-sonner";

const props = defineProps({
    row: { type: Object, required: true },
});

const isDialogOpen = ref(false);
const isLoading = ref(false);

const user = usePage().props.auth.user.name;

const destroy = (slug, productName) => {
    isLoading.value = true;

    router.delete(route("product.destroy", slug), {
        preserveScroll: true,
        showProgress: false,
        onSuccess: () => {
            toast.success("Produk berhasil dihapus", {
                description: `Produk ${productName} berhasil dihapus oleh ${user}`,
            });
        },
        onError: () => {
            toast.error("Produk gagal dihapus");
        },
        onFinish: () => {
            isLoading.value = false;
            isDialogOpen.value = false;
        },
    });
};
</script>

<template>
    <div class="flex items-center justify-center">
        <Button
            variant="ghost"
            size="icon"
            class="group"
            @click="router.get(route('product.edit', row.slug))"
        >
            <Pencil class="w-4 h-4 text-blue-500 dark:group-hover:text-white" />
        </Button>

        <Button
            variant="ghost"
            size="icon"
            class="group"
            @click="router.get(route('product.show', row.slug))"
        >
            <Eye class="w-4 h-4 text-green-500 dark:group-hover:text-white" />
        </Button>

        <AlertDialog v-model:open="isDialogOpen">
            <AlertDialogTrigger>
                <Button
                    variant="ghost"
                    size="icon"
                    class="dark:hover:bg-red-900 hover:bg-red-500 group"
                >
                    <Trash2
                        class="w-4 h-4 text-red-500 dark:group-hover:text-white group-hover:text-black"
                    />
                </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        Apakah anda yakin ingin menghapus product
                        <span class="underline font-bold">{{ row.name }}</span>
                        ini?
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        Data yang telah dihapus tidak dapat dikembalikan!
                        Tindakan ini tidak dapat dibatalkan!
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction
                        @click="destroy(row.slug, row.name)"
                        class="bg-red-500 hover:bg-red-600 text-white"
                    >
                        <Loader2
                            v-if="isLoading"
                            class="w-4 h-4 animate-spin"
                        />
                        {{ isLoading ? "Loading..." : "Hapus" }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
