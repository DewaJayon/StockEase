import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { h } from "vue";
import CategoryActionRow from "./CategoryActionRow.vue";

export const categoryColumns = [
    {
        accessorKey: "name",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Nama Kategori",
            }),
    },
    {
        accessorKey: "action",
        header: "Aksi",
        cell: ({ row }) =>
            h(CategoryActionRow, {
                row: row.original,
            }),
    },
];
