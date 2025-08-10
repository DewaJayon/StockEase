<script setup>
import { Card, CardContent } from "@/Components/ui/card";
import { ref, onMounted, onBeforeUnmount, computed } from "vue";

const isDarkMode = ref(document.documentElement.classList.contains("dark"));
let observer = null;

onMounted(() => {
    observer = new MutationObserver(() => {
        isDarkMode.value = document.documentElement.classList.contains("dark");
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"],
    });
});

onBeforeUnmount(() => {
    if (observer) observer.disconnect();
});

const series = [
    {
        name: "Penjualan",
        data: [
            12000000, 15000000, 17000000, 9000000, 14000000, 18000000, 22000000,
            20000000, 24000000, 21000000, 25000000, 30000000,
        ],
    },
];

const chartOptions = computed(() => ({
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
    chart: {
        background: "transparent",
    },
    toolbar: {
        show: true,
    },
    colors: ["#4f46e5"],
    stroke: {
        curve: "smooth",
        width: 3,
    },
    xaxis: {
        categories: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "Mei",
            "Jun",
            "Jul",
            "Agu",
            "Sep",
            "Okt",
            "Nov",
            "Des",
        ],
    },
    yaxis: {
        labels: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
    tooltip: {
        y: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
}));

const paymentSeries = [44, 55, 13, 43, 22];

const paymentChartOptions = computed(() => ({
    chart: {
        type: "pie",
        background: "transparent",
    },
    labels: ["Team A", "Team B", "Team C", "Team D", "Team E"],
    responsive: [
        {
            breakpoint: 480,
            options: {
                chart: {
                    width: 200,
                },
                legend: {
                    position: "bottom",
                },
            },
        },
    ],
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
}));
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card class="shadow-md">
            <CardContent class="pb-0 pt-3">
                <apexchart
                    type="line"
                    height="350"
                    :options="chartOptions"
                    :series="series"
                />
            </CardContent>
        </Card>
        <Card class="shadow-md">
            <CardContent class="flex justify-center items-center h-full w-full">
                <apexchart
                    type="pie"
                    width="470"
                    :options="paymentChartOptions"
                    :series="paymentSeries"
                />
            </CardContent>
        </Card>
    </div>
</template>
