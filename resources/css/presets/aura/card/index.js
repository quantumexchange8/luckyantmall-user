export default {
    root: {
        class: [
            //Flex
            'flex flex-col',

            //Shape
            'rounded-[12px]',
            'shadow-toast',

            //Color
            'bg-white dark:bg-surface-900',
            'text-surface-700 dark:text-surface-0',
            'border dark:border-surface-600'
        ]
    },
    body: {
        class: [
            //Flex
            'flex flex-col',
            'gap-4',

            'p-3 md:p-6'
        ]
    },
    caption: {
        class: [
            //Flex
            'flex flex-col',
            'gap-2'
        ]
    },
    title: {
        class: 'text-xl font-semibold mb-0'
    },
    subtitle: {
        class: [
            //Font
            'font-normal',

            //Spacing
            'mb-0',

            //Color
            'text-surface-600 dark:text-surface-0/60'
        ]
    },
    content: {
        class: 'p-0'
    },
    footer: {
        class: 'p-0'
    }
};
