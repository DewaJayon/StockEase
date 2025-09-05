import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { h } from "vue";

const centerClass = "capitalize flex items-center justify-center";

export const filteredStockColumns = [
    {
        accessorKey: "#",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "#",
            }),
        cell: ({ row }) => h("span", row.index + 1),
    },
    {
        accessorKey: "name",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Nama Produk",
            }),
    },
    {
        accessorKey: "supplier",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Nama Supplier",
            }),
    },
    {
        accessorKey: "category",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Kategori",
            }),
    },
    {
        accessorKey: "category",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Kategori",
            }),
    },
    {
        accessorKey: "stock",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Stock",
            }),
    },
    {
        accessorKey: "alert_stock",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Stock Minimal",
            }),
    },
    {
        accessorKey: "status",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Status",
            }),
    },
];
