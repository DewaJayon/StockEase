<script setup>
import { Button } from "@/Components/ui/button";
import { Loader2, Trash2 } from "lucide-vue-next";
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
import { ref } from "vue";
import { toast } from "vue-sonner";
import SupplierUpdateForm from "../form/SupplierUpdateForm.vue";

const props = defineProps({
    row: { type: Object, required: true },
});

const isLoading = ref(false);
const isDialogOpen = ref(false);
const user = usePage().props.auth.user.name;

const destroy = (slug) => {
    isLoading.value = true;
    isDialogOpen.value = true;

    router.delete(route("supplier.destroy", slug), {
        preserveScroll: true,
        showProgress: false,
        onSuccess: () => {
            toast.success("Supplier berhasil dihapus", {
                description: `Supplier ${props.row.name} berhasil dihapus oleh ${user}`,
            });
        },
        onError: () => {
            toast.error("Supplier gagal dihapus");
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
        <SupplierUpdateForm :row="row" />

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
                        Apakah anda yakin ingin menghapus?
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        Data yang telah dihapus tidak dapat dikembalikan!
                        Tindakan ini tidak dapat dibatalkan!
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction
                        @click="destroy(row.slug)"
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
