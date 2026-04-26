<script setup>
import { Link } from '@inertiajs/vue3';
import { filterMenuByRole } from '@/lib/utils';
import { ChevronDown } from 'lucide-vue-next';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
} from '@/Components/ui/sidebar';
import {
    CollapsibleContent,
    CollapsibleRoot,
    CollapsibleTrigger,
} from 'reka-ui';
import { computed } from 'vue';

const props = defineProps({
    title: String,
    items: Array,
    userRole: String,
    collapsible: {
        type: Boolean,
        default: false,
    },
});

const filteredItems = computed(() =>
    filterMenuByRole(props.items, props.userRole),
);
const hasItems = computed(() => filteredItems.value.length > 0);
</script>

<template>
  <template v-if="hasItems">
    <!-- Render Collapsible Group -->
    <CollapsibleRoot
      v-if="collapsible"
      default-open
      class="group/collapsible"
    >
      <SidebarGroup>
        <SidebarGroupLabel as-child>
          <CollapsibleTrigger>
            {{ title }}
            <ChevronDown
              class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-180"
            />
          </CollapsibleTrigger>
        </SidebarGroupLabel>
        <CollapsibleContent>
          <SidebarGroupContent>
            <SidebarMenu>
              <SidebarMenuItem
                v-for="item in filteredItems"
                :key="item.title"
              >
                <SidebarMenuButton
                  as-child
                  :is-active="route().current(item.routeName)"
                >
                  <Link
                    :href="
                      route().has(item.routeName)
                        ? route(item.routeName)
                        : '#'
                    "
                  >
                    <component :is="item.icon" />
                    <span>{{ item.title }}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </SidebarMenu>
          </SidebarGroupContent>
        </CollapsibleContent>
      </SidebarGroup>
    </CollapsibleRoot>

    <!-- Render Simple Group -->
    <SidebarGroup v-else>
      <SidebarGroupLabel>{{ title }}</SidebarGroupLabel>
      <SidebarGroupContent>
        <SidebarMenu>
          <SidebarMenuItem
            v-for="item in filteredItems"
            :key="item.title"
          >
            <SidebarMenuButton
              as-child
              :is-active="route().current(item.routeName)"
            >
              <Link
                :href="
                  route().has(item.routeName)
                    ? route(item.routeName)
                    : '#'
                "
              >
                <component :is="item.icon" />
                <span>{{ item.title }}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroupContent>
    </SidebarGroup>
  </template>
</template>
