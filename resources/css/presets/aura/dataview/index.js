export default {
    root: {
        class: [
            'flex flex-col gap-3 md:gap-5'
        ]
    },
    content: {
        class: [
            // Spacing
            'p-0',

            // Shape
            'border-0',

            // Color
            'text-surface-700 dark:text-white/80',
        ]
    },
    header: {
        class: [
            'font-semibold',

            // Spacing
            'py-3 px-4',

            // Color
            'text-surface-800 dark:text-white/80',
            'bg-surface-0 dark:bg-surface-900',

            'rounded-[12px]'
        ]
    }
};
