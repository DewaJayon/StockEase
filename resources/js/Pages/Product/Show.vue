<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { ArrowLeftToLine, Loader2, Pencil, Trash } from "lucide-vue-next";
import { Separator } from "@/Components/ui/separator";
import { formatPrice } from "@/lib/utils";
import { Badge } from "@/Components/ui/badge";
import { toast } from "vue-sonner";
import { ref } from "vue";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";

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

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const stockColor = (stock, alertStock) => {
    if (stock <= 0) {
        return "text-red-500";
    } else if (stock <= alertStock) {
        return "text-yellow-500";
    } else {
        return "text-green-500";
    }
};

const isLoading = ref(false);
const isDialogOpen = ref(false);

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
    <AuthenticatedLayout>
        <Head>
            <title>Product</title>
        </Head>
        <template #breadcrumb>
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <Link :href="route('home')">
                            <BreadcrumbLink> Dashboard </BreadcrumbLink>
                        </Link>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage> Product </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Product</h4>
                    <Link :href="route('product.index')">
                        <Button
                            variant="outline"
                            class="dark:border-white border-zinc-600"
                        >
                            <ArrowLeftToLine />
                            Kembali ke daftar produk
                        </Button>
                    </Link>
                </div>
                <Separator class="my-4" />

                <div class="mt-4">
                    <div class="max-w-7xl mx-auto">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                            <div
                                class="rounded-xl xl:p-8 flex items-center justify-center"
                            >
                                <img
                                    v-if="props.product.image_path"
                                    class="w-full h-full object-cover rounded-xl"
                                    :src="`/${props.product.image_path}`"
                                    :alt="`${props.product.name}`"
                                    loading="lazy"
                                />
                                <img
                                    v-else
                                    class="w-full h-full object-cover rounded-xl"
                                    src="/img/StockEase-Logo.png"
                                    :alt="`${props.product.name}`"
                                    loading="lazy"
                                />
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <div
                                        class="flex items-center space-x-3 mb-2"
                                    >
                                        <Badge class="capitalize">
                                            {{ props.product.category.name }}
                                        </Badge>

                                        <Badge
                                            class="capitalize text-gray-800"
                                            :class="`${
                                                props.product.stock >=
                                                props.product.alert_stock
                                                    ? 'bg-green-500'
                                                    : 'bg-red-500'
                                            }`"
                                        >
                                            {{
                                                props.product.stock >=
                                                props.product.alert_stock
                                                    ? "Tersedia"
                                                    : "Habis"
                                            }}
                                        </Badge>
                                    </div>
                                    <h1
                                        class="text-3xl font-bold leading-snug mb-2"
                                    >
                                        {{ props.product.name }}
                                    </h1>
                                    <p class="text-muted-foreground">
                                        {{ props.product.sku }}
                                    </p>
                                </div>

                                <div class="flex items-center space-x-4">
                                    <span class="text-muted-foreground">
                                        Harga Jual:
                                    </span>
                                    <span class="font-bold">
                                        {{
                                            formatPrice(
                                                props.product.selling_price
                                            )
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center space-x-4">
                                    <span class="text-muted-foreground">
                                        Stock:
                                    </span>
                                    <span
                                        class="font-semibold uppercase"
                                        :class="
                                            stockColor(
                                                props.product.stock,
                                                props.product.alert_stock
                                            )
                                        "
                                    >
                                        {{ props.product.stock }}
                                        {{ props.product.unit }}
                                    </span>
                                </div>

                                <div>
                                    <h1 class="text-xl font-semibold mb-4">
                                        Detail
                                    </h1>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <div
                                            class="flex justify-between py-2 border-b border-gray-700"
                                        >
                                            <span class="text-muted-foreground">
                                                Kategori
                                            </span>
                                            <span class="font-semibold">
                                                {{
                                                    props.product.category.name
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex justify-between py-2 border-b border-gray-700"
                                        >
                                            <span class="text-muted-foreground">
                                                Barcode
                                            </span>
                                            <span class="font-semibold">
                                                {{ props.product.barcode }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex justify-between py-2 border-b border-gray-700"
                                        >
                                            <span class="text-muted-foreground">
                                                Harga Beli
                                            </span>
                                            <span class="font-semibold">
                                                {{
                                                    formatPrice(
                                                        props.product
                                                            .purchase_price
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex justify-between py-2 border-b border-gray-700"
                                        >
                                            <span class="text-muted-foreground">
                                                Stock Minimum
                                            </span>
                                            <span class="font-semibold">
                                                {{ props.product.alert_stock }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <Link
                                        :href="
                                            route(
                                                'product.edit',
                                                props.product.slug
                                            )
                                        "
                                    >
                                        <Button
                                            class="bg-blue-500 hover:bg-blue-600 text-gray-950"
                                        >
                                            <Pencil class="w-4 h-4" />
                                            Edit
                                        </Button>
                                    </Link>

                                    <AlertDialog v-model:open="isDialogOpen">
                                        <AlertDialogTrigger>
                                            <Button variant="destructive">
                                                <Trash class="w-4 h-4" />
                                                Hapus
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>
                                                    Apakah anda yakin ingin
                                                    menghapus product
                                                    <span
                                                        class="underline font-bold"
                                                        >{{
                                                            props.product.name
                                                        }}</span
                                                    >
                                                    ini?
                                                </AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Data yang telah dihapus
                                                    tidak dapat dikembalikan!
                                                    Tindakan ini tidak dapat
                                                    dibatalkan!
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel
                                                    >Batal</AlertDialogCancel
                                                >
                                                <AlertDialogAction
                                                    @click="
                                                        destroy(
                                                            props.product.slug,
                                                            props.product.name
                                                        )
                                                    "
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
