<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
});

const title = computed(() => {
    return (
        {
            503: '503: Service Unavailable',
            500: '500: Server Error',
            404: '404: Page Not Found',
            403: '403: Forbidden',
            401: '401: Unauthorized',
            419: '419: Page Expired',
        }[props.status] || 'Error'
    );
});

const description = computed(() => {
    return (
        {
            503: 'Sorry, we are doing some maintenance. Please check back soon.',
            500: 'Whoops, something went wrong on our servers.',
            404: 'Sorry, the page you are looking for could not be found.',
            403: 'Sorry, you are forbidden from accessing this page.',
            401: 'Please log in to access this page.',
            419: 'The page has expired due to inactivity. Please refresh and try again.',
        }[props.status] || 'An unexpected error occurred.'
    );
});
</script>

<template>
    <Head :title="title" />

    <div
        class="min-h-screen bg-gray-50 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-md w-full text-center space-y-8">
            <div class="space-y-4">
                <h1
                    class="text-9xl font-extrabold text-primary-600 tracking-tight animate-bounce"
                >
                    {{ status }}
                </h1>
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                    {{ title }}
                </h2>
                <p class="text-lg text-gray-600">
                    {{ description }}
                </p>
            </div>

            <div class="flex justify-center space-x-4">
                <Link
                    href="/"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                >
                    Back to Home
                </Link>
                <button
                    class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                    @click="() => window.location.reload()"
                >
                    Refresh Page
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.text-primary-600 {
    color: #4f46e5; /* Default indigo-600, common in Breeze/Inertia projects */
}
.bg-primary-600 {
    background-color: #4f46e5;
}
.hover\:bg-primary-700:hover {
    background-color: #4338ca;
}
.focus\:ring-primary-500:focus {
    --tw-ring-color: #6366f1;
}
</style>
