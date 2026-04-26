import { DataTableColumnHeader } from '@/Components/ui/data-table';
import { formatDate, formatPrice } from '@/lib/utils';
import { h } from 'vue';
import MidtransStatusRow from './MidtransStatusRow.vue';
import MidtransActionRow from './MidtransActionRow.vue';

const centerClass =
    'capitalize flex items-center justify-center text-center w-full';

export const midtransTransactionColumns = [
    {
        accessorKey: 'no',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'No',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h('div', { class: 'text-center w-full' }, row.index + 1),
    },
    {
        accessorKey: 'external_id',
        header: () => h('div', { class: 'text-center w-full' }, 'ID Transaksi'),
        cell: ({ row }) =>
            h('span', { class: centerClass }, row.original.external_id),
    },
    {
        accessorKey: 'status',
        header: () => h('div', { class: 'text-center w-full' }, 'Status'),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'flex justify-center' },
                h(MidtransStatusRow, { row: row.original.status }),
            ),
    },
    {
        accessorKey: 'amount',
        header: () =>
            h('div', { class: 'text-center w-full' }, 'Total Pembayaran'),
        cell: (row) =>
            h(
                'span',
                { class: centerClass },
                formatPrice(row.row.original.amount),
            ),
    },
    {
        accessorKey: 'payment_type',
        header: () =>
            h('div', { class: 'text-center w-full' }, 'Tipe Pembayaran'),
        cell: ({ row }) =>
            h('span', { class: centerClass }, row.original.payment_type),
    },
    {
        accessorKey: 'created_at',
        header: () =>
            h('div', { class: 'text-center w-full' }, 'Tanggal Pembayaran'),
        cell: ({ row }) =>
            h(
                'span',
                { class: centerClass },
                formatDate(row.original.created_at),
            ),
    },
    {
        accessorKey: 'action',
        header: () => h('div', { class: 'text-center w-full' }, 'Aksi'),
        cell: ({ row }) => h(MidtransActionRow, { row: row.original }),
    },
];
