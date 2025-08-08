<script setup>
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card>
            <CardHeader>
                <CardTitle>Card Title</CardTitle>
                <CardDescription>Card Description</CardDescription>
            </CardHeader>
            <CardContent> Card Content </CardContent>
            <CardFooter> Card Footer </CardFooter>
        </Card>
    </div>
</template>
