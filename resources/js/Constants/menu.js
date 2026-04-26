import {
    PackageSearch,
    Tag,
    Truck,
    LayoutDashboard,
    UserRound,
    ShoppingCart,
    ShoppingBag,
    PackageCheck,
    CircleDollarSign,
    FileText,
    FileBox,
    Warehouse,
    Logs,
    File,
    Scale,
    CalendarClock,
    TrendingUp,
} from 'lucide-vue-next';

export const menuSections = [
    {
        label: 'Dashboard',
        collapsible: false,
        items: [
            {
                title: 'Dashboard',
                routeName: 'dashboard',
                icon: LayoutDashboard,
                roles: ['admin', 'cashier', 'warehouse'],
            },
            {
                title: 'User',
                routeName: 'users.index',
                icon: UserRound,
                roles: ['admin'],
            },
            {
                title: 'POS (Kasir)',
                routeName: 'pos.index',
                icon: ShoppingCart,
                roles: ['admin', 'cashier'],
            },
        ],
    },
    {
        label: 'Manajemen Data',
        collapsible: true,
        items: [
            {
                title: 'Produk',
                routeName: 'product.index',
                icon: PackageSearch,
                roles: ['admin', 'warehouse'],
            },
            {
                title: 'Kategori',
                routeName: 'category.index',
                icon: Tag,
                roles: ['admin'],
            },
            {
                title: 'Satuan',
                routeName: 'unit.index',
                icon: Scale,
                roles: ['admin', 'warehouse'],
            },
            {
                title: 'Supplier',
                routeName: 'supplier.index',
                icon: Truck,
                roles: ['admin', 'warehouse'],
            },
        ],
    },
    {
        label: 'Data Transaksi dan Penjualan',
        collapsible: true,
        items: [
            {
                title: 'Pembelian',
                routeName: 'purchase.index',
                icon: PackageCheck,
                roles: ['admin', 'warehouse'],
            },
            {
                title: 'Penjualan',
                routeName: 'sale.index',
                icon: ShoppingBag,
                roles: ['admin', 'cashier'],
            },
            {
                title: 'Transaksi Midtrans',
                routeName: 'midtrans.index',
                icon: CircleDollarSign,
                roles: ['admin', 'cashier'],
            },
        ],
    },
    {
        label: 'Laporan',
        collapsible: true,
        items: [
            {
                title: 'Laporan Penjualan',
                routeName: 'reports.sale.index',
                icon: FileText,
                roles: ['admin', 'cashier'],
            },
            {
                title: 'Laporan Pembelian',
                routeName: 'reports.purchase.index',
                icon: FileBox,
                roles: ['admin', 'warehouse'],
            },
            {
                title: 'Laporan Stock',
                routeName: 'reports.stock.index',
                icon: Warehouse,
                roles: ['admin', 'warehouse'],
            },
            {
                title: 'Laporan Kedaluwarsa',
                routeName: 'reports.expiry.index',
                icon: CalendarClock,
                roles: ['admin', 'warehouse'],
            },
            {
                title: 'Laporan Laba / Rugi',
                routeName: 'reports.profit-loss',
                icon: TrendingUp,
                roles: ['admin'],
            },
        ],
    },
    {
        label: 'Lainnya',
        collapsible: true,
        items: [
            {
                title: 'Stock Opname',
                routeName: 'stock-adjustment.index',
                icon: Scale,
                roles: ['admin', 'warehouse'],
            },
            {
                title: 'Log Stock',
                routeName: 'log-stock.index',
                icon: Logs,
                roles: ['admin', 'warehouse'],
            },
            {
                title: 'File Manager',
                routeName: 'file-manager.index',
                icon: File,
                roles: ['admin', 'cashier', 'warehouse'],
            },
        ],
    },
];
