<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

defineProps({
    links: {
        type: Array,
        required: true,
    },
    from: {
        type: Number,
        default: null,
    },
    to: {
        type: Number,
        default: null,
    },
    total: {
        type: Number,
        default: null,
    },
});

function isPrev(label) {
    return label.toLowerCase().includes('previous') || label.includes('&laquo;') || label.includes('«');
}

function isNext(label) {
    return label.toLowerCase().includes('next') || label.includes('&raquo;') || label.includes('»');
}
</script>

<template>
    <div
        v-if="links && links.length > 3"
        class="flex flex-col items-center justify-between gap-3 border-t px-4 py-3 text-sm sm:flex-row"
    >
        <p v-if="total !== null" class="text-muted-foreground">
            Showing
            <span class="font-medium text-foreground">{{ from ?? 0 }}</span>
            to
            <span class="font-medium text-foreground">{{ to ?? 0 }}</span>
            of
            <span class="font-medium text-foreground">{{ total }}</span>
            results
        </p>

        <nav aria-label="Pagination" class="flex flex-wrap items-center gap-1">
            <template v-for="(link, index) in links" :key="index">
                <span
                    v-if="!link.url"
                    class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border border-transparent px-2.5 text-muted-foreground/50 select-none"
                >
                    <ChevronLeft v-if="isPrev(link.label)" class="h-4 w-4" />
                    <ChevronRight v-else-if="isNext(link.label)" class="h-4 w-4" />
                    <span v-else v-html="link.label" />
                </span>

                <Link
                    v-else
                    :href="link.url"
                    preserve-scroll
                    class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2.5 font-medium transition-colors"
                    :class="
                        link.active
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-input bg-background text-foreground hover:bg-accent hover:text-accent-foreground'
                    "
                >
                    <ChevronLeft v-if="isPrev(link.label)" class="h-4 w-4" />
                    <ChevronRight v-else-if="isNext(link.label)" class="h-4 w-4" />
                    <span v-else v-html="link.label" />
                </Link>
            </template>
        </nav>
    </div>
</template>
