import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { h } from "vue";

const centerClass = "capitalize flex items-center justify-center";

export const purcaseColumns = [
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
            return h("span", { class: centerClass }, row.original.total);
        },
    },
    {
        header: "Jumlah Item",
        cell: ({ row }) => {
            const items = row.original.purcase_items || [];
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
    },
];
