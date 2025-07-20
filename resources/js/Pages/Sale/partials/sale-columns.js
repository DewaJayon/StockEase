import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { h } from "vue";
import { formatPrice, formatDateTime } from "@/lib/utils";
import SaleActionRow from "./SaleActionRow.vue";
import SaleHistoryStatusRow from "./SaleHistoryStatusRow.vue";

const centerClass = "capitalize flex items-center justify-center";

export const saleColumns = [
    {
        accessorKey: "nomor",
        header: "No.",
        cell: ({ row }) => h("span", { class: centerClass }, row.index + 1),
    },
    {
        accessorKey: "created_at",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Tanggal",
            }),
        cell: ({ row }) =>
            h(
                "span",
                { class: centerClass },
                formatDateTime(row.original.updated_at)
            ),
    },
    {
        accessorKey: "user.name",
        header: "Kasir",
        cell: ({ row }) =>
            h("span", { class: centerClass }, row.original.user.name),
    },
    {
        accessorKey: "total",
        header: "Total",
        cell: ({ row }) =>
            h("span", { class: centerClass }, formatPrice(row.original.total)),
    },
    {
        accessorKey: "payment_method",
        header: "Metode Pembayaran",
        cell: ({ row }) =>
            h("span", { class: centerClass }, row.original.payment_method),
    },
    {
        accessorKey: "status",
        header: "Status",
        cell: ({ row }) =>
            h(SaleHistoryStatusRow, { row: row.original.status }),
    },
    {
        accessorKey: "action",
        header: "Aksi",
        cell: ({ row }) => h(SaleActionRow, { row: row.original }),
    },
];
