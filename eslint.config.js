import pluginVue from "eslint-plugin-vue";

export default [
    {
        ignores: [
            "vendor/**",
            "node_modules/**",
            "public/**",
            "storage/**",
            "bootstrap/cache/**",
            "resources/js/Components/ui/*",
        ],
    },
    ...pluginVue.configs["flat/recommended"],
    {
        rules: {
            "vue/multi-word-component-names": "off",
            "vue/no-use-v-if-with-v-for": "warn", // Downgrade to warn for now to allow progress
            "vue/require-default-prop": "off", // Disable for Inertia props
        },
    },
];
