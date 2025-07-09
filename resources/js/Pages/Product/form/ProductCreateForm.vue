<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { nextTick, ref, watch } from "vue";
import { cn } from "@/lib/utils";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";
import VueCropper from "vue-cropperjs";
import "/node_modules/cropperjs/dist/cropper.css";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";

import {
    ArrowLeftToLine,
    Check,
    ChevronsUpDown,
    Loader2,
    Search,
} from "lucide-vue-next";

import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from "@/Components/ui/combobox";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import { toast } from "vue-sonner";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    units: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
});

const category = ref();
const unit = ref();
const showCropperModal = ref(false);

watch(category, (newValue) => {
    form.category_id = newValue.value;
});

watch(unit, (newValue) => {
    form.unit = newValue.value;
});

const form = useForm({
    category_id: "",
    name: "",
    sku: "",
    barcode: "",
    unit: "",
    stock: 0,
    purchase_price: 0,
    selling_price: 0,
    alert_stock: 0,
    image: null,
});

const imgSrc = ref(null);
const cropImg = ref(null);
const cropper = ref(null);

const setImage = (e) => {
    const file = e.target.files[0];
    if (!file?.type?.includes("image/")) {
        alert("Please select an image file");
        return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
        imgSrc.value = event.target.result;
        showCropperModal.value = true;
        nextTick(() => {
            cropper.value?.replace(event.target.result);
        });
    };
    reader.readAsDataURL(file);
};

const handleCrop = () => {
    const canvas = cropper.value?.getCroppedCanvas();

    if (canvas) {
        cropImg.value = canvas.toDataURL();

        canvas.toBlob((blob) => {
            if (blob) {
                const file = new File([blob], "cropped.jpg", {
                    type: "image/jpeg",
                });

                form.image = file;
            }
        }, "image/jpeg");
    }

    showCropperModal.value = false;
};

const user = usePage().props.auth.user.name;

const submit = () => {
    form.post(route("product.store"), {
        showProgress: false,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success("Produk berhasil ditambahkan", {
                description: `Produk ${form.name} berhasil ditambahkan oleh ${user}`,
            });

            showCropperModal.value = false;
        },
        onError: () => {
            toast.error("Produk gagal ditambahkan");
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>Tambah Produk</title>
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
                        <Link :href="route('product.index')">
                            <BreadcrumbLink> Produk </BreadcrumbLink>
                        </Link>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage> Tambah Produk </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Tambah Produk</h4>
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
                    <form
                        @submit.prevent="submit"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >
                        <div>
                            <Label for="name">Nama Produk</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Contoh: Indomie Goreng"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div>
                            <Label for="category">Kategori</Label>
                            <Combobox v-model="category" by="label">
                                <ComboboxAnchor class="w-full">
                                    <ComboboxTrigger as-child class="w-full">
                                        <Button
                                            variant="outline"
                                            class="justify-between"
                                        >
                                            {{
                                                category?.label ??
                                                "Pilih kategori"
                                            }}

                                            <ChevronsUpDown
                                                class="ml-2 h-4 w-4 shrink-0 opacity-50"
                                            />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>

                                <ComboboxList>
                                    <div
                                        class="relative w-full max-w-sm items-center"
                                    >
                                        <ComboboxInput
                                            class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10"
                                            placeholder="Cari kategori..."
                                        />
                                        <span
                                            class="absolute start-0 inset-y-0 flex items-center justify-center px-3"
                                        >
                                            <Search
                                                class="size-4 text-muted-foreground"
                                            />
                                        </span>
                                    </div>

                                    <ComboboxEmpty>
                                        Tidak ada kategori ditemukan.
                                    </ComboboxEmpty>

                                    <ComboboxGroup>
                                        <ComboboxItem
                                            v-for="category in categories"
                                            :key="category.value"
                                            :value="category"
                                            class="cursor-pointer"
                                        >
                                            {{ category.label }}

                                            <ComboboxItemIndicator>
                                                <Check
                                                    :class="
                                                        cn('ml-auto h-4 w-4')
                                                    "
                                                />
                                            </ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>

                            <InputError :message="form.errors.category_id" />
                        </div>

                        <div>
                            <Label for="sku">SKU</Label>
                            <Input
                                id="sku"
                                v-model="form.sku"
                                type="text"
                                placeholder="Contoh: SKU001"
                            />

                            <InputError :message="form.errors.sku" />
                        </div>

                        <div>
                            <Label for="barcode">Barcode</Label>
                            <Input
                                id="barcode"
                                v-model="form.barcode"
                                type="text"
                                placeholder="Opsional"
                            />

                            <InputError :message="form.errors.barcode" />
                        </div>

                        <div>
                            <Label for="unit">Satuan</Label>
                            <Combobox v-model="unit" by="label">
                                <ComboboxAnchor class="w-full">
                                    <ComboboxTrigger as-child class="w-full">
                                        <Button
                                            variant="outline"
                                            class="justify-between"
                                        >
                                            {{ unit?.label ?? "Pilih satuan" }}

                                            <ChevronsUpDown
                                                class="ml-2 h-4 w-4 shrink-0 opacity-50"
                                            />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>

                                <ComboboxList>
                                    <div
                                        class="relative w-full max-w-sm items-center"
                                    >
                                        <ComboboxInput
                                            class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10"
                                            placeholder="Cari satuan..."
                                        />
                                        <span
                                            class="absolute start-0 inset-y-0 flex items-center justify-center px-3"
                                        >
                                            <Search
                                                class="size-4 text-muted-foreground"
                                            />
                                        </span>
                                    </div>

                                    <ComboboxEmpty>
                                        Tidak ada satuan ditemukan.
                                    </ComboboxEmpty>

                                    <ComboboxGroup>
                                        <ComboboxItem
                                            v-for="unit in units"
                                            :key="unit.value"
                                            :value="unit"
                                            class="cursor-pointer"
                                        >
                                            {{ unit.label }}

                                            <ComboboxItemIndicator>
                                                <Check
                                                    :class="
                                                        cn('ml-auto h-4 w-4')
                                                    "
                                                />
                                            </ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>

                            <InputError :message="form.errors.unit" />
                        </div>

                        <div>
                            <Label for="stock">Stok</Label>
                            <Input
                                id="stock"
                                v-model="form.stock"
                                type="number"
                                min="0"
                                class="[&::-webkit-inner-spin-button]:appearance-none"
                            />
                            <InputError :message="form.errors.stock" />
                        </div>

                        <div>
                            <Label for="purchase_price">Harga Beli</Label>
                            <Input
                                id="purchase_price"
                                v-model="form.purchase_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="[&::-webkit-inner-spin-button]:appearance-none"
                            />
                            <InputError :message="form.errors.purchase_price" />
                        </div>

                        <div>
                            <Label for="selling_price">Harga Jual</Label>
                            <Input
                                id="selling_price"
                                v-model="form.selling_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="[&::-webkit-inner-spin-button]:appearance-none"
                            />
                            <InputError :message="form.errors.selling_price" />
                        </div>

                        <div>
                            <Label for="alert_stock">Stok Minimum</Label>
                            <Input
                                id="alert_stock"
                                v-model="form.alert_stock"
                                type="number"
                                min="0"
                                class="[&::-webkit-inner-spin-button]:appearance-none"
                            />
                            <InputError :message="form.errors.alert_stock" />
                        </div>

                        <div>
                            <Label for="image">Gambar Produk</Label>
                            <Input
                                type="file"
                                id="image"
                                accept="image/*"
                                @change="setImage"
                            />
                            <img
                                v-if="cropImg"
                                :src="cropImg"
                                class="mt-2 rounded border"
                                style="
                                    width: 150px;
                                    height: 150px;
                                    object-fit: cover;
                                "
                            />
                            <InputError :message="form.errors.image" />
                        </div>

                        <div class="col-span-full flex justify-end">
                            <Button type="submit" :disabled="form.processing">
                                <Loader2
                                    v-if="form.processing"
                                    class="w-4 h-4 animate-spin"
                                />
                                Simpan Produk
                            </Button>
                        </div>
                    </form>

                    <Dialog v-model:open="showCropperModal">
                        <DialogContent class="max-w-3xl">
                            <DialogHeader>
                                <DialogTitle>Crop Gambar</DialogTitle>
                            </DialogHeader>

                            <div class="flex flex-col gap-4">
                                <vue-cropper
                                    ref="cropper"
                                    :src="imgSrc"
                                    :guides="true"
                                    :view-mode="2"
                                    drag-mode="crop"
                                    :auto-crop-area="0.5"
                                    :min-container-width="250"
                                    :min-container-height="180"
                                    :background="true"
                                    :rotatable="true"
                                    :aspectRatio="1 / 1"
                                    :img-style="{
                                        width: '100%',
                                        maxHeight: '400px',
                                    }"
                                />

                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        @click="showCropperModal = false"
                                    >
                                        Batal
                                    </Button>
                                    <Button @click="handleCrop"
                                        >Simpan Gambar</Button
                                    >
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
