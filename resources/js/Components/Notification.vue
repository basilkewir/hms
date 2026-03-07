<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="opacity-100 translate-y-0 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            :class="[
                'max-w-md w-full min-w-[320px] shadow-lg rounded-lg pointer-events-auto',
                getNotificationClasses()
            ]"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <component :is="getIcon()" :class="['h-6 w-6', getIconColor()]" />
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p :class="['text-sm font-medium break-words', getTextColor()]">
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button
                            @click="close"
                            :class="['inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2', getCloseButtonClasses()]"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    message: {
        type: String,
        required: true
    },
    type: {
        type: String,
        default: 'success',
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    duration: {
        type: Number,
        default: 5000
    }
});

const emit = defineEmits(['close']);

const show = ref(false);

onMounted(() => {
    show.value = true;

    if (props.duration > 0) {
        setTimeout(() => {
            close();
        }, props.duration);
    }
});

const close = () => {
    show.value = false;
    setTimeout(() => {
        emit('close');
    }, 200);
};

const getNotificationClasses = () => {
    const classes = {
        success: 'bg-kotel-dark/95 border-2 border-kotel-yellow/60 backdrop-blur-xl shadow-xl shadow-kotel-yellow/20',
        error: 'bg-kotel-dark/95 border-2 border-red-500/60 backdrop-blur-xl shadow-xl shadow-red-500/20',
        warning: 'bg-kotel-dark/95 border-2 border-yellow-500/60 backdrop-blur-xl shadow-xl shadow-yellow-500/20',
        info: 'bg-kotel-dark/95 border-2 border-kotel-sky-blue/60 backdrop-blur-xl shadow-xl shadow-kotel-sky-blue/20'
    };
    return classes[props.type] || classes.success;
};

const getTextColor = () => {
    const colors = {
        success: 'text-kotel-yellow font-semibold',
        error: 'text-red-400 font-semibold',
        warning: 'text-yellow-400 font-semibold',
        info: 'text-kotel-sky-blue font-semibold'
    };
    return colors[props.type] || colors.success;
};

const getCloseButtonClasses = () => {
    const classes = {
        success: 'text-kotel-yellow hover:text-kotel-yellow/80 focus:ring-kotel-yellow',
        error: 'text-red-400 hover:text-red-300 focus:ring-red-500',
        warning: 'text-yellow-400 hover:text-yellow-300 focus:ring-yellow-500',
        info: 'text-kotel-sky-blue hover:text-kotel-sky-blue/80 focus:ring-kotel-sky-blue'
    };
    return classes[props.type] || classes.success;
};

const getIcon = () => {
    const icons = {
        success: CheckCircleIcon,
        error: XCircleIcon,
        warning: ExclamationTriangleIcon,
        info: InformationCircleIcon
    };
    return icons[props.type] || icons.success;
};

const getIconColor = () => {
    const colors = {
        success: 'text-kotel-yellow',
        error: 'text-red-400',
        warning: 'text-yellow-400',
        info: 'text-kotel-sky-blue'
    };
    return colors[props.type] || colors.success;
};
</script>
