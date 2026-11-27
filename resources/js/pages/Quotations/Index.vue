<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import quotationRoutes from '@/routes/quotations';
import Card from '@/components/ui/card/Card.vue';
import Button from '@/components/ui/button/Button.vue';
import Pagination from '@/components/Pagination.vue';
import { FileText, Plus, Download, Loader2, SquarePen } from '@lucide/vue';
import Delete from './Delete.vue';
import Filters from './Filters.vue';
import PreviewPdf from '@/components/PreviewPdf.vue';

defineProps({
    quotations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Quotations',
                href: '/quotation',
            },
        ],
    },
});

// Track the specific quotation ID currently downloading
const exportingId = ref(null);

function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return Number.isNaN(date.getTime())
        ? dateString
        : date.toLocaleDateString('en-PK', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
        });
}

function getFilenameFromHeader(disposition, fallback) {
    if (!disposition || !disposition.includes('filename=')) {
        return fallback;
    }
    return disposition
        .split('filename=')[1]
        .replace(/['"]/g, '')
        .trim();
}

const exportToPdf = async (quotation) => {
    if (!quotation?.id || exportingId.value === quotation.id) return;

    exportingId.value = quotation.id;

    try {
        const response = await axios({
            url: quotationRoutes.download.url({ id: quotation.id }),
            method: 'GET',
            responseType: 'blob',
        });

        // Guard against IDM / download manager 204 interceptions
        if (response.status === 204 || response.data.size === 0) {
            return;
        }

        const fallbackName = `quotation-${quotation.quotation_number ?? quotation.id}.pdf`;
        const filename = getFilenameFromHeader(
            response.headers['content-disposition'],
            fallbackName
        );

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('PDF export failed:', error);
    } finally {
        exportingId.value = null;
    }
};
</script>

<template>
    <Head title="Quotations" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Quotations</h1>
                <p class="text-sm text-muted-foreground">
                    Manage customer quotations, pricing, and validity dates.
                </p>
            </div>

            <Button variant="default" as-child>
                <Link :href="quotationRoutes.create.url()">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Create Quotation
                </Link>
            </Button>
        </div>

        <Card class="overflow-hidden p-0">
            <Filters :filters="filters" />
            <div class="overflow-x-auto">
                <table class="w-full caption-bottom border-collapse text-left text-sm">
                    <thead
                        class="border-b bg-muted/50 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Quotation #</th>
                            <th class="px-4 py-3">Customer Name</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Validity</th>
                            <th class="px-4 py-3">Created By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr
                            v-for="(quotation, index) in quotations.data"
                            :key="quotation.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td class="px-4 py-3 font-medium text-muted-foreground">
                                {{ (quotations?.from ?? 1) + index }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ quotation?.quotation_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ quotation?.company?.name ?? '—' }}
                                <span v-if="quotation?.company?.branch">
                                    ({{ quotation.company.branch }})
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatDate(quotation?.date) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ quotation?.validity }} days
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground"
                                >
                                    {{ quotation?.user?.name ?? 'System' }}
                                </span>
                            </td>
                            <td class="flex items-center justify-end gap-1 px-4 py-3 text-right">
                                <Link
                                    :href="quotationRoutes?.edit?.url?.({ quotation: quotation.id })"
                                    title="Edit Quotation"
                                >
                                    <SquarePen class="h-4 w-4 text-blue-900" />
                                </Link>
                                <PreviewPdf
                                    :preview-url="quotationRoutes.download.url({ id: quotation.id })"
                                    :download-url="quotationRoutes.download.url({ id: quotation.id })"
                                    :filename="`quotation-${quotation.quotation_number ?? quotation.id}.pdf`"
                                    :title="`Quotation ${quotation.quotation_number ?? ''}`"
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    :disabled="exportingId === quotation.id"
                                    :title="`Download PDF for ${quotation?.quotation_number ?? 'Quotation'}`"
                                    @click="exportToPdf(quotation)"
                                    class="cursor-pointer"
                                >
                                    <Loader2
                                        v-if="exportingId === quotation.id"
                                        class="mr-1.5 h-4 w-4 animate-spin"
                                    />
                                    <Download v-else class="h-4 w-4" />
                                </Button>
                                <Delete :id="quotation?.id" />
                            </td>
                        </tr>

                        <tr v-if="!quotations.data?.length">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                        <FileText class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <p class="font-medium">No quotations found</p>
                                    <p class="text-xs text-muted-foreground">
                                        Get started by creating your first quotation.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="quotations.data?.length"
                :links="quotations.links"
                :from="quotations.from"
                :to="quotations.to"
                :total="quotations.total"
            />
        </Card>
    </div>
</template>
