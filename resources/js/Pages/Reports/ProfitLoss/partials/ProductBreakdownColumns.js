import { h } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { DataTableColumnHeader } from '@/Components/ui/data-table';

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

export const columns = [
    {
        accessorKey: 'product_name',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Produk',
            }),
        cell: ({ row }) => {
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium' }, row.original.product_name),
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground' },
                    row.original.sku,
                ),
            ]);
        },
    },
    {
        accessorKey: 'total_qty',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Terjual',
                class: 'justify-center',
            }),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'text-center font-medium' },
                row.original.total_qty,
            ),
    },
    {
        accessorKey: 'revenue',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Pendapatan',
                class: 'justify-end',
            }),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'text-right' },
                formatCurrency(row.original.revenue),
            ),
    },
    {
        accessorKey: 'cost',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'HPP (Modal)',
                class: 'justify-end',
            }),
        cell: ({ row }) =>
            h(
                'div',
                { class: 'text-right text-muted-foreground' },
                formatCurrency(row.original.cost),
            ),
    },
    {
        accessorKey: 'profit',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Laba Kotor',
                class: 'justify-end',
            }),
        cell: ({ row }) => {
            const profit = row.original.profit;
            return h(
                'div',
                {
                    class: `text-right font-bold ${
                        profit >= 0 ? 'text-emerald-600' : 'text-red-600'
                    }`,
                },
                formatCurrency(profit),
            );
        },
    },
    {
        id: 'margin',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: 'Margin',
                class: 'justify-center',
            }),
        cell: ({ row }) => {
            const profit = row.original.profit;
            const revenue = row.original.revenue;
            const margin = revenue > 0 ? (profit / revenue) * 100 : 0;

            return h('div', { class: 'flex justify-center' }, [
                h(
                    Badge,
                    {
                        variant: profit > 0 ? 'secondary' : 'destructive',
                        class: 'font-mono',
                    },
                    () => `${margin.toFixed(1)}%`,
                ),
            ]);
        },
    },
];
