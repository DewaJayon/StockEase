<script setup>
import { Button } from "@/Components/ui/button";
import { Loader2, Plus } from "lucide-vue-next";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { toast } from "vue-sonner";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

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

const form = useForm({
    name: "",
});

const user = usePage().props.auth.user.name;

const isDialogOpen = ref(false);

const submit = () => {
    alert("submit");
};
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger as-child>
            <Button variant="outline" class="dark:border-white border-zinc-600">
                <Plus />
                Tambah Kategori
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Form tambah kategori</DialogTitle>
                <DialogDescription>
                    Silahkan isi form dibawah ini untuk menambahkan kategori
                </DialogDescription>
            </DialogHeader>
            <form id="form" @submit.prevent="submit">
                <div class="flex items-center space-x-2">
                    <div class="grid flex-1 gap-2">
                        <Label for="name"> Nama Kategori </Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Masukkan nama kategori"
                            type="text"
                            required
                            autocomplete="off"
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
