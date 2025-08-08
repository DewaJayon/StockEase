<script setup>
import DatePicker from "@/Components/DatePicker.vue";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";

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
    <Card>
        <CardHeader>
            <CardTitle>
                <h1 class="text-2xl font-bold">Laporan Penjualan</h1>
            </CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-1">
                <Label html-for="startDate">Tanggal Mulai</Label>
                <DatePicker label="Tanggal" class="w-full" id="startDate" />
            </div>
            <div class="space-y-1">
                <Label html-for="endDate">Tanggal Selesai</Label>
                <DatePicker label="Tanggal" class="w-full" id="endDate" />
            </div>
            <div class="space-y-1">
                <Label html-for="cashier">Kasir</Label>
                <Select id="cashier">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Pilih Kasir" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Fruits</SelectLabel>
                            <SelectItem value="apple"> Apple </SelectItem>
                            <SelectItem value="banana"> Banana </SelectItem>
                            <SelectItem value="blueberry">
                                Blueberry
                            </SelectItem>
                            <SelectItem value="grapes"> Grapes </SelectItem>
                            <SelectItem value="pineapple">
                                Pineapple
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
            <div class="space-y-1">
                <Label html-for="payment">Metode Pembayaran</Label>
                <Select id="payment">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Pilih Metode Pembayaran" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Fruits</SelectLabel>
                            <SelectItem value="apple"> Apple </SelectItem>
                            <SelectItem value="banana"> Banana </SelectItem>
                            <SelectItem value="blueberry">
                                Blueberry
                            </SelectItem>
                            <SelectItem value="grapes"> Grapes </SelectItem>
                            <SelectItem value="pineapple">
                                Pineapple
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>

            <div class="flex space-x-2">
                <Button>Lihat Laporan</Button>
                <Button>Lihat Laporan</Button>
                <Button>Lihat Laporan</Button>
            </div>
        </CardContent>
    </Card>
</template>
