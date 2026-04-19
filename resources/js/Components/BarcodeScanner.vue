<script setup>
import { ref, watch, nextTick, onUnmounted } from "vue";
import { Html5QrcodeScanner } from "html5-qrcode";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(["update:show", "result"]);

let html5QrcodeScanner = null;

const onScanSuccess = (decodedText, decodedResult) => {
    emit("result", decodedText);
};

const onScanFailure = (error) => {
    // console.warn(`Code scan error = ${error}`);
};

const clearScanner = () => {
    if (html5QrcodeScanner) {
        html5QrcodeScanner
            .clear()
            .then(() => {
                html5QrcodeScanner = null;
            })
            .catch((err) => {
                console.error("Failed to clear scanner", err);
            });
    }
};

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            nextTick(() => {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader",
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    false,
                );
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            });
        } else {
            clearScanner();
        }
    },
);

onUnmounted(() => {
    clearScanner();
});
</script>

<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
        <DialogContent class="sm:max-w-106.25">
            <DialogHeader>
                <DialogTitle>Scan Barcode</DialogTitle>
            </DialogHeader>
            <div id="reader" width="600px"></div>
        </DialogContent>
    </Dialog>
</template>
