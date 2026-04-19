<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";
import { toast } from "vue-sonner";
import InputError from "@/Components/InputError.vue";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { ArrowLeftToLine, Loader2, History } from "lucide-vue-next";
import { format } from "date-fns";
import { id } from "date-fns/locale";

import { formatPrice } from "@/lib/utils";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    history: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    purchase_price: parseFloat(props.product.purchase_price) || 0,
    selling_price: parseFloat(props.product.selling_price) || 0,
    reason: "",
});

const user = usePage().props.auth.user.name;

const submit = () => {
    // Ensure payload values are numeric
    const payload = {
        ...form.data(),
        purchase_price: parseFloat(form.purchase_price) || 0,
        selling_price: parseFloat(form.selling_price) || 0,
    };

    form.transform((data) => payload).patch(
        route("product.price.update", props.product.slug),
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Harga produk berhasil diperbarui", {
                    description: `Harga produk ${props.product.name} berhasil diperbarui oleh ${user}`,
                });
                form.reason = "";
            },
            onError: () => {
                toast.error("Gagal memperbarui harga produk");
            },
        },
    );
};

const formatDate = (date) => {
    return format(new Date(date), "dd MMMM yyyy HH:mm", { locale: id });
};

const formatInput = (val) => {
    if (val === null || val === undefined || val === "") return "";
    let str = val.toString().replace(".", ",");
    let parts = str.split(",");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    return parts.join(",");
};

const parseInput = (val) => {
    if (val === null || val === undefined) return "";
    let clean = val.toString().replace(/[^\d,]/g, "");
    const commaIndex = clean.indexOf(",");
    if (commaIndex !== -1) {
        clean =
            clean.slice(0, commaIndex + 1) +
            clean.slice(commaIndex + 1).replace(/,/g, "");
    }
    return clean.replace(",", ".");
};
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>Update Harga - {{ product.name }}</title>
        </Head>
        <template #breadcrumb>
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <Link :href="route('dashboard')">
                            <BreadcrumbLink> Dashboard </BreadcrumbLink>
                        </Link>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <Link :href="route('product.index')">
                            <BreadcrumbLink> Produk </BreadcrumbLink>
                        </Link>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage> Update Harga </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>

        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Update Price Form -->
                <div class="lg:col-span-1">
                    <div class="rounded-xl bg-muted/50 p-4 h-full">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-semibold text-lg">Update Harga</h4>
                            <Link :href="route('product.index')">
                                <Button variant="outline" size="sm">
                                    <ArrowLeftToLine class="mr-2 h-4 w-4" />
                                    Kembali
                                </Button>
                            </Link>
                        </div>
                        <Separator class="mb-6" />

                        <div
                            class="mb-6 bg-white dark:bg-zinc-900 p-4 rounded-lg border"
                        >
                            <p class="text-sm text-muted-foreground mb-1">
                                Produk
                            </p>
                            <p class="font-medium text-lg">
                                {{ product.name }}
                            </p>
                            <p class="text-xs font-mono text-muted-foreground">
                                {{ product.sku }}
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <Label for="purchase_price"
                                    >Harga Beli Baru</Label
                                >
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground font-medium"
                                        >Rp</span
                                    >
                                    <Input
                                        id="purchase_price"
                                        :model-value="
                                            formatInput(form.purchase_price)
                                        "
                                        @update:model-value="
                                            (v) =>
                                                (form.purchase_price =
                                                    parseInput(v))
                                        "
                                        type="text"
                                        class="pl-9 font-mono"
                                    />
                                </div>
                                <InputError
                                    :message="form.errors.purchase_price"
                                />
                            </div>

                            <div>
                                <Label for="selling_price"
                                    >Harga Jual Baru</Label
                                >
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground font-medium"
                                        >Rp</span
                                    >
                                    <Input
                                        id="selling_price"
                                        :model-value="
                                            formatInput(form.selling_price)
                                        "
                                        @update:model-value="
                                            (v) =>
                                                (form.selling_price =
                                                    parseInput(v))
                                        "
                                        type="text"
                                        class="pl-9 font-mono text-blue-600 dark:text-blue-400"
                                    />
                                </div>
                                <InputError
                                    :message="form.errors.selling_price"
                                />
                            </div>

                            <div>
                                <Label for="reason">Alasan Perubahan</Label>
                                <Textarea
                                    id="reason"
                                    v-model="form.reason"
                                    placeholder="Contoh: Kenaikan harga dari supplier"
                                    rows="3"
                                />
                                <p
                                    class="text-[10px] text-muted-foreground mt-1"
                                >
                                    Wajib diisi untuk keperluan audit.
                                </p>
                                <InputError :message="form.errors.reason" />
                            </div>

                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing"
                            >
                                <Loader2
                                    v-if="form.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                Update Harga
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Price History -->
                <div class="lg:col-span-2">
                    <div class="rounded-xl bg-muted/50 p-4 h-full">
                        <div class="flex items-center gap-2 mb-4">
                            <History class="h-5 w-5 text-muted-foreground" />
                            <h4 class="font-semibold text-lg">
                                Riwayat Perubahan Harga
                            </h4>
                        </div>
                        <Separator class="mb-4" />

                        <div
                            class="rounded-md border bg-white dark:bg-zinc-900 overflow-hidden"
                        >
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Tanggal</TableHead>
                                        <TableHead>Oleh</TableHead>
                                        <TableHead
                                            >Harga Beli (Lama → Baru)</TableHead
                                        >
                                        <TableHead
                                            >Harga Jual (Lama → Baru)</TableHead
                                        >
                                        <TableHead>Alasan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="log in history.data"
                                        :key="log.id"
                                    >
                                        <TableCell class="whitespace-nowrap">
                                            {{ formatDate(log.created_at) }}
                                        </TableCell>
                                        <TableCell>{{
                                            log.user.name
                                        }}</TableCell>
                                        <TableCell class="text-xs">
                                            <div class="flex flex-col">
                                                <span
                                                    class="line-through text-muted-foreground"
                                                    >{{
                                                        formatPrice(
                                                            log.old_purchase_price,
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    class="font-medium text-green-600 dark:text-green-400"
                                                    >{{
                                                        formatPrice(
                                                            log.new_purchase_price,
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-xs">
                                            <div class="flex flex-col">
                                                <span
                                                    class="line-through text-muted-foreground"
                                                    >{{
                                                        formatPrice(
                                                            log.old_selling_price,
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    class="font-medium text-blue-600 dark:text-blue-400"
                                                    >{{
                                                        formatPrice(
                                                            log.new_selling_price,
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                        </TableCell>
                                        <TableCell
                                            class="max-w-50 truncate"
                                            :title="log.reason"
                                        >
                                            {{ log.reason }}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="history.data.length === 0">
                                        <TableCell
                                            colspan="5"
                                            class="text-center py-10 text-muted-foreground"
                                        >
                                            Belum ada riwayat perubahan harga.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div
                            v-if="history.links.length > 3"
                            class="mt-4 flex justify-end gap-2"
                        >
                            <Link
                                v-for="(link, index) in history.links"
                                :key="index"
                                :href="link.url || '#'"
                                v-html="link.label"
                                class="px-3 py-1 text-sm rounded border"
                                :class="{
                                    'bg-primary text-primary-foreground':
                                        link.active,
                                    'opacity-50 cursor-not-allowed': !link.url,
                                    'hover:bg-muted': link.url && !link.active,
                                }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
