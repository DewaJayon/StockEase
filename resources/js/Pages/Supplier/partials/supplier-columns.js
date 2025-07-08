import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { h } from "vue";
import SupplierActionRow from "./SupplierActionRow.vue";

export const supplierColumns = [
    {
        accessorKey: "name",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Nama Supplier",
            }),
    },
    {
        accessorKey: "phone",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Nomor Telepon",
            }),
    },
    {
        accessorKey: "address",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Alamat Supplier",
            }),
    },
    {
        accessorKey: "action",
        header: "Aksi",
        cell: ({ row }) =>
            h(SupplierActionRow, {
                row: row.original,
            }),
    },
];
