<script setup>
import { Link } from "@inertiajs/vue3";
</script>
<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10"
    >
        <div class="flex w-full max-w-sm flex-col gap-6">
            <Link
                href="#"
                class="flex items-center gap-2 self-center font-medium"
            >
                <div class="flex items-center gap-2">
                    <img
                        class="w-14 h-14 rounded-xl shadow-xl"
                        src="/img/StockEase-Logo.png"
                        alt="Stock Ease"
                    />
                    <span class="text-lg font-bold">Stock Ease</span>
                </div>
            </Link>
            <slot />
        </div>
    </div>
</template>
