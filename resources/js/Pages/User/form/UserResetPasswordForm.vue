<script setup>
import { Button } from "@/Components/ui/button";
import { Eye, Loader2 } from "lucide-vue-next";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";
import { toast } from "vue-sonner";

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

const props = defineProps({
    row: { type: Object, required: true },
});

const form = useForm({
    password: "",
});

const user = usePage().props.auth.user.name;
const isDialogOpen = ref(false);

const showPassword = (input) => {
    const passwordInput = document.getElementById(input);

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
    } else {
        passwordInput.type = "password";
    }
};

const submit = (id, name) => {
    form.put(route("users.reset-password", id), {
        showProgress: false,
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Password ${name} berhasil diperbarui`, {
                description: `User ${name} berhasil diperbarui oleh ${user}`,
            });
            isDialogOpen.value = false;
            form.reset();
        },
        onError: () => {
            toast.error(`Password ${name} gagal diperbarui`);
        },
    });
};
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger as-child>
            <Button variant="destructive">Reset Password</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Form reset password user</DialogTitle>
                <DialogDescription>
                    Silahkan isi form dibawah ini untuk reset password user
                </DialogDescription>
            </DialogHeader>
            <form
                id="reset-password-form"
                @submit.prevent="submit(row.id, row.name)"
                class="space-y-4"
            >
                <div class="flex items-center space-x-2">
                    <div class="grid flex-1 gap-2">
                        <Label html-for="password">Password</Label>

                        <div class="relative">
                            <Input
                                id="password"
                                type="password"
                                placeholder="••••••••"
                                v-model="form.password"
                                required
                                class="pr-10"
                            />

                            <span
                                class="absolute right-0 inset-y-0 flex items-center px-3"
                            >
                                <Eye
                                    class="cursor-pointer"
                                    @click="showPassword('password')"
                                />
                            </span>
                        </div>

                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>
                </div>
            </form>
            <DialogFooter class="flex">
                <DialogClose as-child>
                    <Button type="button" variant="secondary">Batal</Button>
                </DialogClose>

                <Button
                    type="submit"
                    form="reset-password-form"
                    :class="{ 'opacity-25 ': form.processing }"
                    :disabled="form.processing"
                    variant="destructive"
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
