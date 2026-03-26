<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 p-4 md:p-8 lg:p-12 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900"
    >
        <!-- Animated background particles -->
        <div class="absolute inset-0 opacity-[0.15]">
            <div
                class="animate-blob absolute top-10 left-10 h-20 w-20 rounded-full bg-gradient-to-r from-blue-400/40 to-purple-400/40 blur-xl"
            ></div>
            <div
                class="animate-blob animation-delay-2000 absolute top-40 right-20 h-32 w-32 rounded-full bg-gradient-to-r from-purple-400/40 to-pink-400/40 blur-xl"
            ></div>
            <div
                class="animate-blob animation-delay-4000 absolute bottom-20 left-[25%] h-24 w-24 rounded-full bg-gradient-to-r from-pink-400/40 to-blue-400/40 blur-xl"
            ></div>
        </div>

        <div
            class="relative z-10 mx-auto w-full max-w-md animate-in rounded-3xl border border-white/50 bg-white/70 p-8 shadow-2xl backdrop-blur-xl duration-1000 fade-in slide-in-from-top-8 md:p-10 dark:border-slate-200/40 dark:bg-slate-800/60"
        >
            <div class="flex flex-col items-center gap-6 md:gap-8">
                <!-- Enhanced Logo -->
                <Link
                    :href="home()"
                    class="group flex flex-col items-center gap-3 rounded-2xl p-4 transition-all duration-300 hover:scale-105 hover:bg-white/50 dark:hover:bg-slate-700/50"
                >
                    <div
                        class="relative flex h-16 w-16 animate-pulse items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 p-4 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl"
                    >
                        <AppLogoIcon
                            class="size-10 fill-current text-white drop-shadow-md"
                        />
                    </div>
                    <span class="sr-only">{{ title }}</span>
                </Link>

                <!-- Title & Description -->
                <div
                    class="animate-in space-y-3 text-center delay-200 duration-1000 fade-in slide-in-from-top-4"
                >
                    <h1
                        class="bg-gradient-to-r from-gray-900 to-slate-700 bg-clip-text text-3xl font-bold text-transparent drop-shadow-lg md:text-4xl dark:from-white dark:to-slate-200"
                    >
                        {{ title }}
                    </h1>
                    <p
                        class="mx-auto max-w-sm text-lg leading-relaxed text-muted-foreground/90 backdrop-blur-sm md:text-xl"
                    >
                        {{ description }}
                    </p>
                </div>

                <!-- Form content slot -->
                <div
                    class="w-full animate-in delay-400 duration-1000 fade-in zoom-in"
                >
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
