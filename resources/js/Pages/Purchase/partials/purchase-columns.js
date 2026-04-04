import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { formatPrice } from "@/lib/utils";
import { h } from "vue";
import PurchaseActionRow from "./PurchaseActionRow.vue";

const centerClass = "capitalize flex items-center justify-center";

export const purchaseColumns = [
    {
        accessorKey: "date",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Tanggal Pembelian",
            }),
    },
    {
        accessorKey: "supplier.name",
        header: "Supplier",
        cell: ({ row }) => {
            return h(
                "span",
                { class: centerClass },
                row.original.supplier.name
            );
        },
    },
    {
        accessorKey: "user.name",
        header: "User yang input data",
        cell: ({ row }) => {
            return h("span", { class: centerClass }, row.original.user.name);
        },
    },
    {
        accessorKey: "total",
        header: "Total Pembelian",
        cell: ({ row }) => {
            return h(
                "span",
                { class: centerClass },
                formatPrice(row.original.total)
            );
        },
    },
    {
        header: "Jumlah Item",
        cell: ({ row }) => {
            const items = row.original.purchase_items || [];
            const totalQty = items.reduce(
                (sum, item) => sum + Number(item.qty),
                0
            );

            return h("span", { class: centerClass }, totalQty);
        },
    },
    {
        accessorKey: "action",
        header: "Aksi",
        cell: ({ row }) =>
            h(PurchaseActionRow, {
                row: row.original,
            }),
    },
];
