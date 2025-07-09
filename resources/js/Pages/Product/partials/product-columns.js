import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { formatPrice } from "@/lib/utils";
import { h } from "vue";
import ProductImageRow from "./ProductImageRow.vue";
import ProductActionRow from "./ProductActionRow.vue";

export const productColumns = [
    {
        accessorKey: "image_path",
        header: "Gambar",
        enableSorting: false,
        cell: ({ row }) => h(ProductImageRow, { row: row.original }),
    },
    {
        accessorKey: "name",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Nama",
            }),
    },
    {
        accessorKey: "category.name",
        header: h(
            "span",
            {
                class: "flex items-start justify-start ",
            },
            {
                default: () => "Kategori",
            }
        ),
    },
    {
        accessorKey: "sku",
        header: h(
            "span",
            {
                class: "flex items-start justify-start ",
            },
            {
                default: () => "SKU",
            }
        ),
    },
    {
        accessorKey: "unit",
        header: h(
            "span",
            {
                class: "flex items-start justify-start ",
            },
            {
                default: () => "Satuan",
            }
        ),
        cell: ({ row }) =>
            h(
                "span",
                { class: "uppercase" },
                { default: () => row.original.unit }
            ),
    },
    {
        accessorKey: "stock",
        header: h(
            "span",
            {
                class: "flex items-start justify-start ",
            },
            {
                default: () => "Stock",
            }
        ),
    },
    {
        accessorKey: "alert_stock",
        header: h(
            "span",
            {
                class: "flex items-start justify-start ",
            },
            {
                default: () => "Stok Minimal",
            }
        ),
    },
    {
        accessorKey: "selling_price",
        header: h(
            "span",
            {
                class: "flex items-start justify-start ",
            },
            {
                default: () => "Harga Jual",
            }
        ),
        cell: ({ row }) => formatPrice(row.original.selling_price),
    },
    {
        accessorKey: "action",
        header: "Aksi",
        cell: ({ row }) =>
            h(ProductActionRow, {
                row: row.original,
            }),
    },
];
