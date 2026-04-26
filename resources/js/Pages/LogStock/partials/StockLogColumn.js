import { DataTableColumnHeader } from '@/Components/ui/data-table';
import { h } from 'vue';
import { formatDate } from '@/lib/utils';
import QtyRow from './QtyRow.vue';

export const stockLogColumns = [
    {
        accessorKey: 'created_at',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Tanggal',
            }),
        cell: ({ row }) => h('span', null, formatDate(row.original.updated_at)),
    },
    {
        accessorKey: 'product.name',
        header: 'Produk',
    },
    {
        accessorKey: 'type',
        header: 'Tipe',
        cell: ({ row }) =>
            h('span', { class: 'capitalize' }, row.original.type),
    },
    {
        accessorKey: 'qty',
        header: 'Qty',
        cell: ({ row }) => h(QtyRow, { row: row.original }),
    },
    {
        accessorKey: 'reference_type',
        header: 'Referensi',
        cell: ({ row }) =>
            h('span', { class: 'capitalize' }, row.original.reference_type),
    },

    {
        accessorKey: 'note',
        header: 'Catatan',
        cell: ({ row }) =>
            h(
                'p',
                {
                    class: 'max-w-[200px]',
                    title: row.original.note,
                },
                row.original.note,
            ),
    },
];
