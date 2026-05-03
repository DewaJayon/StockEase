import { DataTableColumnHeader } from '@/Components/ui/data-table';
import { Badge } from '@/Components/ui/badge';
import { h } from 'vue';
import PromotionActionRow from './PromotionActionRow.vue';

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(dateString));
};

const formatValue = (row) => {
    if (row.type === 'percentage') {
        return `${Number(row.discount_value).toFixed(0)}%`;
    }
    if (row.type === 'nominal') {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(row.discount_value);
    }
    if (row.type === 'bogo') {
        return `Beli ${row.buy_qty} Gratis ${row.get_qty}`;
    }
    return '-';
};

const typeLabel = {
    percentage: 'Persentase',
    nominal: 'Nominal',
    bogo: 'BOGO',
};

export const promotionColumns = (categories, products) => [
    {
        accessorKey: 'name',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Nama Promo' }),
    },
    {
        accessorKey: 'type',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Tipe' }),
        cell: ({ row }) =>
            h(
                Badge,
                { variant: 'outline', class: 'w-fit capitalize' },
                () => typeLabel[row.original.type] ?? row.original.type,
            ),
    },
    {
        accessorKey: 'discount_value',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Nilai' }),
        cell: ({ row }) =>
            h(
                'span',
                { class: 'text-sm font-medium' },
                formatValue(row.original),
            ),
    },
    {
        accessorKey: 'target',
        header: () => h('div', {}, 'Target'),
        cell: ({ row }) => {
            const promo = row.original;
            if (promo.product) {
                return h(
                    'span',
                    { class: 'text-sm' },
                    `Produk: ${promo.product.name}`,
                );
            }
            if (promo.category) {
                return h(
                    'span',
                    { class: 'text-sm' },
                    `Kategori: ${promo.category.name}`,
                );
            }
            return h(
                'span',
                { class: 'text-sm text-muted-foreground' },
                'Semua Produk',
            );
        },
    },
    {
        accessorKey: 'period',
        header: () => h('div', {}, 'Periode'),
        cell: ({ row }) =>
            h(
                'span',
                { class: 'text-sm' },
                `${formatDate(row.original.start_date)} – ${formatDate(row.original.end_date)}`,
            ),
    },
    {
        accessorKey: 'is_active',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Status' }),
        cell: ({ row }) =>
            h(
                Badge,
                { variant: row.original.is_active ? 'default' : 'secondary' },
                () => (row.original.is_active ? 'Aktif' : 'Nonaktif'),
            ),
    },
    {
        accessorKey: 'action',
        header: () => h('div', { class: 'text-center w-full' }, 'Aksi'),
        cell: ({ row }) =>
            h(PromotionActionRow, {
                row: row.original,
                categories,
                products,
            }),
    },
];
