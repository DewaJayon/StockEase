<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { Head, Link } from '@inertiajs/vue3';
import { Separator } from '@/Components/ui/separator';
import { DataTable } from '@/Components/ui/data-table';
import { unitColumns } from './partials/unit-column';
import UnitCreateForm from './form/UnitCreateForm.vue';

const props = defineProps({
    units: {
        type: Object,
        required: true,
    },
});
</script>

<template>
  <AuthenticatedLayout>
    <Head>
      <title>Satuan</title>
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
            <BreadcrumbPage> Satuan </BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>
    </template>
    <div class="flex flex-1 flex-col gap-4 p-4">
      <div class="rounded-xl bg-muted/50 h-full p-4">
        <div class="flex justify-between items-center">
          <h4 class="font-semibold">
            Satuan
          </h4>
          <UnitCreateForm />
        </div>
        <Separator class="my-4" />

        <div class="mt-4">
          <DataTable
            :data="units.data"
            :columns="unitColumns"
            :route-name="'unit.index'"
            :pagination="units"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
