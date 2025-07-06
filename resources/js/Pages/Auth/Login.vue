<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Loader2 } from "lucide-vue-next";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head>
            <title>Login</title>
        </Head>
        <div class="flex flex-col gap-6">
            <Card>
                <CardHeader class="text-center">
                    <CardTitle class="text-xl"> Selamat Datang </CardTitle>
                    <CardDescription>
                        Silahkan login untuk melanjutkan
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit">
                        <div class="grid gap-6">
                            <div class="grid gap-6">
                                <div class="grid gap-2">
                                    <Label html-for="email">Email</Label>
                                    <Input
                                        v-model="form.email"
                                        id="email"
                                        type="email"
                                        placeholder="m@gmail.com"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.email"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label html-for="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        placeholder="password"
                                        v-model="form.password"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.password"
                                    />
                                </div>
                                <Button
                                    type="submit"
                                    class="w-full"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <Loader2
                                        v-if="form.processing"
                                        class="w-4 h-4 animate-spin"
                                    />
                                    Login
                                </Button>
                            </div>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </GuestLayout>
</template>
