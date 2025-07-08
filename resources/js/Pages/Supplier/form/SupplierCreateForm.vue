<script setup>
import { Button } from "@/Components/ui/button";
import { Loader2, Plus } from "lucide-vue-next";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { toast } from "vue-sonner";
import { Textarea } from "@/Components/ui/textarea";
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
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";

const form = useForm({
    name: "",
    phone: "",
    address: "",
});

const user = usePage().props.auth.user.name;
const isDialogOpen = ref(false);

const submit = () => {
    form.post(route("supplier.store"), {
        showProgress: false,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success("Supplier berhasil ditambahkan", {
                description: `Supplier ${form.name} berhasil ditambahkan oleh ${user}`,
            });
            isDialogOpen.value = false;
        },
        onError: () => {
            toast.error("Supplier gagal ditambahkan");
        },
    });
};
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger as-child>
            <Button variant="outline" class="dark:border-white border-zinc-600">
                <Plus />
                Tambah Supplier
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Form tambah supplier</DialogTitle>
                <DialogDescription>
                    Silahkan isi form dibawah ini untuk menambahkan supplier
                </DialogDescription>
            </DialogHeader>
            <form id="form" @submit.prevent="submit" class="space-y-4">
                <div class="flex items-center space-x-2">
                    <div class="grid flex-1 gap-2">
                        <Label for="name"> Nama supplier </Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Masukkan nama supplier"
                            type="text"
                            required
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="grid flex-1 gap-2">
                        <Label for="phone"> Nomor Telepon </Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            placeholder="Masukkan nomor telepon"
                            type="text"
                            inputmode="numeric"
                            required
                            autocomplete="off"
                            class="appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                        />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="grid flex-1 gap-2">
                        <Label for="address"> Alamat supplier </Label>
                        <Textarea
                            placeholder="Masukkan alamat supplier"
                            id="address"
                            v-model="form.address"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.address"
                        />
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
                    :class="{ 'opacity-25 ': form.processing }"
                    :disabled="form.processing"
                    class="disabled:cursor-not-allowed"
                >
                    <Loader2
                        v-if="form.processing"
                        class="w-4 h-4 animate-spin"
                    />
                    {{ form.processing ? "Loading..." : "Simpan" }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
