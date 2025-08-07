import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { formatDate, formatPrice } from "@/lib/utils";
import { h } from "vue";
import MidtransStatusRow from "./MidtransStatusRow.vue";
import MidtransActionRow from "./MidtransActionRow.vue";

const centerClass = "capitalize flex items-center justify-center";

export const midtransTransactionColumns = [
    {
        accessorKey: "no",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "No",
            }),
        cell: ({ row }) => h("span", row.index + 1),
    },
    {
        accessorKey: "external_id",
        header: "ID Transaksi",
        cell: ({ row }) =>
            h("span", { class: centerClass }, row.original.external_id),
    },
    {
        accessorKey: "status",
        header: "Status",
        cell: ({ row }) => h(MidtransStatusRow, { row: row.original.status }),
    },
    {
        accessorKey: "amount",
        header: "Total Pembayaran",
        cell: (row) =>
            h(
                "span",
                { class: centerClass },
                formatPrice(row.row.original.amount)
            ),
    },
    {
        accessorKey: "payment_type",
        header: "Tipe Pembayaran",
        cell: ({ row }) =>
            h("span", { class: centerClass }, row.original.payment_type),
    },
    {
        accessorKey: "created_at",
        header: "Tanggal Pembayaran",
        cell: ({ row }) =>
            h(
                "span",
                { class: centerClass },
                formatDate(row.original.created_at)
            ),
    },
    {
        accessorKey: "action",
        header: "Aksi",
        cell: ({ row }) => h(MidtransActionRow, { row: row.original }),
    },
];
