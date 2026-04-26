<script setup>
import { Button } from '@/Components/ui/button';
import { Loader2, Plus } from 'lucide-vue-next';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { toast } from 'vue-sonner';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    short_name: '',
});

const user = usePage().props.auth.user.name;

const isDialogOpen = ref(false);

const submit = () => {
    form.post(route('unit.store'), {
        showProgress: false,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Satuan berhasil ditambahkan', {
                description: `Satuan ${form.name} berhasil ditambahkan oleh ${user}`,
            });
            isDialogOpen.value = false;
        },
        onError: () => {
            toast.error('Satuan gagal ditambahkan');
        },
    });
};
</script>

<template>
  <Dialog v-model:open="isDialogOpen">
    <DialogTrigger as-child>
      <Button
        variant="outline"
        class="dark:border-white border-zinc-600"
      >
        <Plus />
        Tambah Satuan
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>Form tambah satuan</DialogTitle>
        <DialogDescription>
          Silahkan isi form dibawah ini untuk menambahkan satuan
        </DialogDescription>
      </DialogHeader>
      <form
        id="form"
        class="space-y-4"
        @submit.prevent="submit"
      >
        <div class="grid gap-2">
          <Label for="name"> Nama Satuan </Label>
          <Input
            id="name"
            v-model="form.name"
            placeholder="Contoh: Kilogram"
            type="text"
            required
            autocomplete="off"
          />
          <span
            v-if="form.errors.name"
            class="text-sm text-red-500"
          >{{ form.errors.name }}</span>
        </div>
        <div class="grid gap-2">
          <Label for="short_name"> Singkatan </Label>
          <Input
            id="short_name"
            v-model="form.short_name"
            placeholder="Contoh: kg"
            type="text"
            required
            autocomplete="off"
          />
          <span
            v-if="form.errors.short_name"
            class="text-sm text-red-500"
          >{{ form.errors.short_name }}</span>
        </div>
      </form>
      <DialogFooter class="flex justify-between">
        <DialogClose as-child>
          <Button
            type="button"
            variant="secondary"
          >
            Batal
          </Button>
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
          {{ form.processing ? 'Loading...' : 'Simpan' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
