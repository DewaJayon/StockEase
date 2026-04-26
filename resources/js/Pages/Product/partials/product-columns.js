import { DataTableColumnHeader } from '@/Components/ui/data-table';
import { formatPrice } from '@/lib/utils';
import { h } from 'vue';
import ProductImageRow from './ProductImageRow.vue';
import ProductActionRow from './ProductActionRow.vue';

export const productColumns = [
    {
        accessorKey: 'image_path',
        header: () => h('div', { class: 'text-center w-full' }, 'Gambar'),
        enableSorting: false,
        cell: ({ row }) =>
            h(
                'div',
                { class: 'flex justify-center' },
                h(ProductImageRow, { row: row.original }),
            ),
    },
    {
        accessorKey: 'name',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Nama',
            }),
    },
    {
        accessorKey: 'category.name',
        header: 'Kategori',
    },
    {
        accessorKey: 'sku',
        header: 'SKU',
    },
    {
        accessorKey: 'unit',
        header: 'Satuan',
        cell: ({ row }) =>
            h(
                'span',
                { class: 'uppercase' },
                { default: () => row.original.unit?.name ?? '-' },
            ),
    },
    {
        accessorKey: 'stock',
        header: () => h('div', { class: 'text-center w-full' }, 'Stock'),
        cell: ({ row }) =>
            h('div', { class: 'text-center w-full' }, row.original.stock),
    },
    {
        accessorKey: 'alert_stock',
        header: () => h('div', { class: 'text-center w-full' }, 'Stok Minimal'),
        cell: ({ row }) =>
            h('div', { class: 'text-center w-full' }, row.original.alert_stock),
    },
    {
        accessorKey: 'selling_price',
        header: 'Harga Jual',
        cell: ({ row }) => formatPrice(row.original.selling_price),
    },
    {
        accessorKey: 'action',
        header: () => h('div', { class: 'text-center w-full' }, 'Aksi'),
        cell: ({ row }) =>
            h(ProductActionRow, {
                row: row.original,
            }),
    },
];
