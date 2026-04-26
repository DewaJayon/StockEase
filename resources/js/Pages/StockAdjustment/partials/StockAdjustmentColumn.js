import { h } from 'vue';
import dayjs from 'dayjs';
import 'dayjs/locale/id';

export const stockAdjustmentColumns = [
    {
        accessorKey: 'date',
        header: 'Tanggal',
        cell: ({ row }) => {
            return h(
                'div',
                null,
                dayjs(row.getValue('date')).locale('id').format('DD MMMM YYYY'),
            );
        },
    },
    {
        accessorKey: 'product.name',
        header: 'Produk',
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'font-medium' },
                row.original.product.name,
            );
        },
    },
    {
        accessorKey: 'old_stock',
        header: 'Stok Lama',
        cell: ({ row }) => {
            return h('div', null, row.getValue('old_stock'));
        },
    },
    {
        accessorKey: 'new_stock',
        header: 'Stok Baru',
        cell: ({ row }) => {
            return h('div', null, row.getValue('new_stock'));
        },
    },
    {
        accessorKey: 'diff',
        header: 'Selisih',
        cell: ({ row }) => {
            const diff = row.original.new_stock - row.original.old_stock;
            const color =
                diff > 0 ? 'text-green-600' : diff < 0 ? 'text-red-600' : '';
            const prefix = diff > 0 ? '+' : '';
            return h(
                'div',
                { class: `font-medium ${color}` },
                `${prefix}${diff}`,
            );
        },
    },
    {
        accessorKey: 'reason',
        header: 'Alasan',
        cell: ({ row }) => {
            return h('div', null, row.getValue('reason') || '-');
        },
    },
    {
        accessorKey: 'user.name',
        header: 'Petugas',
        cell: ({ row }) => {
            return h('div', null, row.original.user.name);
        },
    },
];
