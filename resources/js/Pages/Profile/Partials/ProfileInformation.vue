<script setup>
import { Button } from "@/Components/ui/button";
import { Pencil } from "lucide-vue-next";
</script>

<template>
    <div
        class="mb-6 rounded-2xl border border-gray-200 p-5 lg:p-6 dark:border-gray-800"
    >
        <div
            class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between"
        >
            <div class="flex w-full flex-col items-center gap-6 xl:flex-row">
                <div
                    class="h-20 w-20 overflow-hidden rounded-full border border-gray-200 dark:border-gray-800"
                >
                    <img
                        src="https://dummyimage.com/200x200/000/fff"
                        alt="user"
                    />
                </div>
                <div class="order-3 xl:order-2">
                    <h4
                        class="mb-2 text-center text-lg font-semibold text-gray-800 xl:text-left dark:text-white/90"
                    >
                        {{ $page.props.auth.user.name }}
                    </h4>
                    <div
                        class="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $page.props.auth.user.email }}
                        </p>
                    </div>
                </div>
            </div>

            <Button
                variant="secondary"
                class="shadow-theme-xs flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 lg:inline-flex lg:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
                <Pencil class="w-4 h-4" />
                Edit
            </Button>
        </div>
    </div>
</template>
