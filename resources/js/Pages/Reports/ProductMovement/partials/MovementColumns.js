import { h } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { DataTableColumnHeader } from '@/Components/ui/data-table';

export const fastMovingColumns = [
    {
        id: 'rank',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: '#',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'text-center font-bold text-muted-foreground' },
                row.index + 1,
            ),
    },
    {
        accessorKey: 'product_name',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Produk',
            }),
        cell: ({ row }) =>
            h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium' }, row.original.product_name),
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground' },
                    row.original.sku,
                ),
            ]),
    },
    {
        accessorKey: 'total_qty_sold',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Qty Terjual',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h('div', { class: 'flex justify-center' }, [
                h(
                    Badge,
                    {
                        class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-mono',
                    },
                    () => `${row.original.total_qty_sold} pcs`,
                ),
            ]),
    },
    {
        accessorKey: 'current_stock',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Stok Saat Ini',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'text-center text-muted-foreground' },
                `${row.original.current_stock} pcs`,
            ),
    },
    {
        accessorKey: 'total_revenue',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Pendapatan',
                class: 'justify-end',
            }),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'text-right font-medium' },
                new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                }).format(row.original.total_revenue),
            ),
    },
];

export const slowMovingColumns = [
    {
        id: 'rank',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: '#',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'text-center font-bold text-muted-foreground' },
                row.index + 1,
            ),
    },
    {
        accessorKey: 'product_name',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Produk',
            }),
        cell: ({ row }) =>
            h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium' }, row.original.product_name),
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground' },
                    row.original.sku,
                ),
            ]),
    },
    {
        accessorKey: 'current_stock',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Stok Mengendap',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h('div', { class: 'flex justify-center' }, [
                h(
                    Badge,
                    {
                        class: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 font-mono',
                    },
                    () => `${row.original.current_stock} pcs`,
                ),
            ]),
    },
    {
        accessorKey: 'total_qty_sold',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Qty Terjual',
                class: 'justify-center',
            }),
        cell: ({ row }) => {
            const qty = row.original.total_qty_sold;
            return h(
                'div',
                {
                    class: `text-center font-medium ${qty === 0 ? 'text-red-500' : 'text-muted-foreground'}`,
                },
                qty === 0 ? 'Belum terjual' : `${qty} pcs`,
            );
        },
    },
    {
        accessorKey: 'last_sold_at',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Terakhir Terjual',
                class: 'justify-center',
            }),
        cell: ({ row }) => {
            const date = row.original.last_sold_at;
            return h(
                'div',
                { class: 'text-center text-muted-foreground text-sm' },
                date
                    ? new Intl.DateTimeFormat('id-ID', {
                          day: 'numeric',
                          month: 'short',
                          year: 'numeric',
                      }).format(new Date(date))
                    : '—',
            );
        },
    },
];
