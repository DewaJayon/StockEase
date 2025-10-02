<script setup>
import ProfilePicture from "./ProfilePicture.vue";
import PhotoProfileForm from "./PhotoProfileForm.vue";
import { computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";

const page = usePage();
const photoProfile = computed(() => page.props.auth.user.photo_profile);

const reloadPage = () => {
    router.reload({
        preserveScroll: true,
        preserveState: true,
        only: ["auth"],
    });
};
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
                    <ProfilePicture v-if="!photoProfile" />
                    <img
                        v-else
                        :src="`/${photoProfile}`"
                        class="h-full w-full"
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

            <PhotoProfileForm @photo-updated="reloadPage" />
        </div>
    </div>
</template>
