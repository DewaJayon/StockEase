<script setup>
import { reactiveOmit } from "@vueuse/core";
import { ChevronRight } from "lucide-vue-next";
import { CalendarNext, useForwardProps } from "reka-ui";
import { cn } from "@/lib/utils";
import { buttonVariants } from '@/Components/ui/button';

const props = defineProps({
  nextPage: { type: Function, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: [String, Object, Function], required: false },
  class: { type: null, required: false },
});

const delegatedProps = reactiveOmit(props, "class");

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <CalendarNext
    :class="
      cn(
        buttonVariants({ variant: 'outline' }),
        'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100',
        props.class,
      )
    "
    v-bind="forwardedProps"
  >
    <slot>
      <ChevronRight class="h-4 w-4" />
    </slot>
  </CalendarNext>
</template>
