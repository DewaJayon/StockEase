<script setup>
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
const page = usePage();
const user = computed(() => page.props.auth.user ?? null);

function getColorFromName(name) {
    const colors = [
        "bg-red-500",
        "bg-orange-500",
        "bg-amber-500",
        "bg-yellow-500",
        "bg-lime-500",
        "bg-green-500",
        "bg-emerald-500",
        "bg-teal-500",
        "bg-cyan-500",
        "bg-sky-500",
        "bg-blue-500",
        "bg-indigo-500",
        "bg-violet-500",
        "bg-purple-500",
        "bg-pink-500",
        "bg-rose-500",
    ];

    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }

    const index = Math.abs(hash) % colors.length;
    return colors[index];
}

let avatarColor;
if (user.value) {
    avatarColor = getColorFromName(user.value.name);
}
</script>

<template>
    <div
        class="w-20 h-20 overflow-hidden border rounded-full border-gray-800 flex items-center justify-center"
    >
        <Avatar class="h-full w-full">
            <AvatarImage v-if="user.avatar" :src="user.avatar" />
            <AvatarFallback
                :class="[
                    avatarColor,
                    'text-white font-bold w-full h-full flex items-center justify-center rounded-full',
                ]"
            >
                {{
                    user.name
                        .split(" ")
                        .map((n) => n[0])
                        .join("")
                        .toUpperCase()
                }}
            </AvatarFallback>
        </Avatar>
    </div>
</template>
