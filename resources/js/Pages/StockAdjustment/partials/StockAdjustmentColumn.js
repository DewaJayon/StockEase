import { h } from "vue";
import dayjs from "dayjs";
import "dayjs/locale/id";

const centerHeader = (title) =>
    h("div", { class: "text-center w-full" }, title);

export const stockAdjustmentColumns = [
    {
        accessorKey: "date",
        header: () => centerHeader("Tanggal"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-center" },
                dayjs(row.getValue("date")).locale("id").format("DD MMMM YYYY"),
            );
        },
    },
    {
        accessorKey: "product.name",
        header: () => centerHeader("Produk"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-center font-medium" },
                row.original.product.name,
            );
        },
    },
    {
        accessorKey: "old_stock",
        header: () => centerHeader("Stok Lama"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-center" },
                row.getValue("old_stock"),
            );
        },
    },
    {
        accessorKey: "new_stock",
        header: () => centerHeader("Stok Baru"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-center" },
                row.getValue("new_stock"),
            );
        },
    },
    {
        accessorKey: "diff",
        header: () => centerHeader("Selisih"),
        cell: ({ row }) => {
            const diff = row.original.new_stock - row.original.old_stock;
            const color =
                diff > 0 ? "text-green-600" : diff < 0 ? "text-red-600" : "";
            const prefix = diff > 0 ? "+" : "";
            return h(
                "div",
                { class: `font-medium text-center ${color}` },
                `${prefix}${diff}`,
            );
        },
    },
    {
        accessorKey: "reason",
        header: () => centerHeader("Alasan"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-center" },
                row.getValue("reason") || "-",
            );
        },
    },
    {
        accessorKey: "user.name",
        header: () => centerHeader("Petugas"),
        cell: ({ row }) => {
            return h("div", { class: "text-center" }, row.original.user.name);
        },
    },
];
