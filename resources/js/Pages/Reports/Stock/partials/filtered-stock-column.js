import { DataTableColumnHeader } from '@/Components/ui/data-table';
import { h } from 'vue';
import StockRow from './StockRow.vue';

const centerClass =
    'capitalize flex items-center justify-center text-center w-full';

export const filteredStockColumns = [
    {
        accessorKey: '#',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: '#',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h('div', { class: 'text-center w-full' }, row.index + 1),
    },
    {
        accessorKey: 'name',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Nama Produk',
            }),
    },
    {
        accessorKey: 'supplier',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Nama Supplier',
            }),
    },
    {
        accessorKey: 'category',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Kategori',
            }),
    },
    {
        accessorKey: 'stock',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Stock',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h('div', { class: 'text-center w-full' }, row.original.stock),
    },
    {
        accessorKey: 'alert_stock',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Stock Minimal',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h('div', { class: 'text-center w-full' }, row.original.alert_stock),
    },
    {
        accessorKey: 'status',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Status',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'flex justify-center' },
                h(StockRow, { row: row.original }),
            ),
    },
];
