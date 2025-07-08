<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Head, Link } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";
import { DataTable } from "@/Components/ui/data-table";
import { userColumns } from "./partials/user-columns";
import UserCreateForm from "./form/UserCreateForm.vue";

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>User</title>
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
                        <BreadcrumbPage> User </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">User</h4>
                    <UserCreateForm />
                </div>
                <Separator class="my-4" />

                <div class="mt-4">
                    <DataTable
                        :data="users.data"
                        :columns="userColumns"
                        :route-name="'users.index'"
                        :pagination="users"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
