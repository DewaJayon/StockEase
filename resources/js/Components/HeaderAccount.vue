<script setup>
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
const user = page.props.auth.user;

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

const avatarColor = getColorFromName(user.name);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child class="outline-none cursor-pointer">
            <Avatar
                class="h-8 w-8 flex items-center justify-center rounded-full overflow-hidden"
            >
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
        </DropdownMenuTrigger>
        <DropdownMenuContent>
            <DropdownMenuLabel>Akun Saya</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <Link :href="route('profile.edit')">
                <DropdownMenuItem class="cursor-pointer hover:text-black">
                    Profile
                </DropdownMenuItem>
            </Link>
            <Link
                :href="route('logout')"
                as="button"
                method="post"
                class="w-full hover:bg-accent hover:text-slate-900 cursor-pointer transition-all ease-in-out duration-300 rounded"
            >
                <DropdownMenuItem class="cursor-pointer">
                    Logout
                </DropdownMenuItem>
            </Link>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
