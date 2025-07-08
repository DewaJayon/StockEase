import { DataTableColumnHeader } from "@/Components/ui/data-table";
import { h } from "vue";
import UserActionRow from "./UserActionRow.vue";
import UserRoleRow from "./UserRoleRow.vue";

export const userColumns = [
    {
        accessorKey: "name",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Nama",
            }),
    },
    {
        accessorKey: "email",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Email",
            }),
    },
    {
        accessorKey: "role",
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column: column,
                title: "Role",
            }),
        cell: ({ row }) =>
            h(UserRoleRow, {
                row: row.original,
            }),
    },
    {
        accessorKey: "action",
        header: "Aksi",
        cell: ({ row }) =>
            h(UserActionRow, {
                row: row.original,
            }),
    },
];
