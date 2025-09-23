<script setup>
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
</script>

<template>
    <Select>
        <SelectTrigger>
            <SelectValue placeholder="Select a fruit" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectLabel>Fruits</SelectLabel>
                <SelectItem value="apple"> Apple </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>
