import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { h } from "vue";

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
    },
    {
        accessorKey: "user.name",
        header: "User yang input data",
    },
    {
        accessorKey: "total",
        header: "Total Pembelian",
    },
    {
        accessorKey: "purcase_items.qty",
        header: "Jumlah Item",
    },
    {
        accessorKey: "action",
        header: "Aksi",
    },
];
