<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import sesRoutes from '@/routes/ses';
import Card from '@/components/ui/card/Card.vue';
import Button from '@/components/ui/button/Button.vue';
import Pagination from '@/components/Pagination.vue';
import { FileText, Plus, SquarePen, Download, Loader2 } from '@lucide/vue';
import Delete from './Delete.vue';
import Filters from './Filters.vue';
import PreviewPdf from '@/components/PreviewPdf.vue';

defineProps({
    sesRecords: {
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
                title: 'SES',
                href: '/ses',
            },
        ],
    },
});

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

const exportToPdf = async (record) => {
    if (!record?.id || exportingId.value === record.id) return;

    exportingId.value = record.id;

    try {
        const response = await axios({
            url: sesRoutes.download.url({ id: record.id }),
            method: 'GET',
            responseType: 'blob',
        });

        if (response.status === 204 || response.data.size === 0) {
            return;
        }

        const fallbackName = `ses-${record.document_number ?? record.id}.pdf`;
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
    <Head title="SES" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">SES Documents</h1>
                <p class="text-sm text-muted-foreground">
                    Manage Service Entry Sheets (SES), PO references, and gate passes.
                </p>
            </div>

            <Button variant="default" as-child>
                <Link :href="sesRoutes.create.url()">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Create SES
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
                            <th class="px-4 py-3">Document #</th>
                            <th class="px-4 py-3">Customer Name</th>
                            <th class="px-4 py-3">PO Number</th>
                            <th class="px-4 py-3">RGP / IGP</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Created By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr
                            v-for="(record, index) in sesRecords.data"
                            :key="record.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td class="px-4 py-3 font-medium text-muted-foreground">
                                {{ (sesRecords?.from ?? 1) + index }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ record?.document_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ record?.company?.name ?? '—' }}
                                <span v-if="record?.company?.branch">
                                    ({{ record.company.branch }})
                                </span>
                            </td>
                            <td class="px-4 py-3 text-foreground">
                                {{ record?.po_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ record?.rgp || '—' }} / {{ record?.igp || '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatDate(record?.date) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground"
                                >
                                    {{ record?.user?.name ?? 'System' }}
                                </span>
                            </td>
                            <td class="flex items-center justify-end gap-1 px-4 py-3 text-right">
                                <Link
                                    :href="sesRoutes?.edit?.url?.({ ses: record.id })"
                                    title="Edit SES"
                                >
                                    <SquarePen class="h-4 w-4 text-blue-900" />
                                </Link>
                                <PreviewPdf
                                    :preview-url="sesRoutes.download.url({ id: record.id })"
                                    :download-url="sesRoutes.download.url({ id: record.id })"
                                    :filename="`ses-${record.document_number ?? record.id}.pdf`"
                                    :title="`SES ${record.document_number ?? ''}`"
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    :disabled="exportingId === record.id"
                                    :title="`Download PDF for ${record?.document_number ?? 'SES'}`"
                                    @click="exportToPdf(record)"
                                    class="cursor-pointer"
                                >
                                    <Loader2
                                        v-if="exportingId === record.id"
                                        class="mr-1.5 h-4 w-4 animate-spin"
                                    />
                                    <Download v-else class="h-4 w-4" />
                                </Button>
                                <Delete :id="record?.id" />
                            </td>
                        </tr>

                        <tr v-if="!sesRecords.data?.length">
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                        <FileText class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <p class="font-medium">No SES records found</p>
                                    <p class="text-xs text-muted-foreground">
                                        Get started by creating your first SES document.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="sesRecords.data?.length"
                :links="sesRecords.links"
                :from="sesRecords.from"
                :to="sesRecords.to"
                :total="sesRecords.total"
            />
        </Card>
    </div>
</template>
