import { DataTableColumnHeader } from '@/Components/ui/data-table';
import { h } from 'vue';
import UnitActionRow from './UnitActionRow.vue';

export const unitColumns = [
    {
        accessorKey: 'name',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Nama Satuan',
            }),
    },
    {
        accessorKey: 'short_name',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Singkatan',
            }),
    },
    {
        accessorKey: 'action',
        header: () => h('div', { class: 'text-center w-full' }, 'Aksi'),
        cell: ({ row }) =>
            h(UnitActionRow, {
                row: row.original,
            }),
    },
];
