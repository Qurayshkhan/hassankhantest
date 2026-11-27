<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import invoiceRoutes from '@/routes/invoices';
import Card from '@/components/ui/card/Card.vue';
import Button from '@/components/ui/button/Button.vue';
import Pagination from '@/components/Pagination.vue';
import { FileText, Plus, SquarePen, Download, Loader2 } from '@lucide/vue';
import Delete from './Delete.vue';
import Filters from './Filters.vue';
import PreviewPdf from '@/components/PreviewPdf.vue';

defineProps({
    invoices: {
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
                title: 'Invoices',
                href: '/invoices',
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

const exportToPdf = async (invoice) => {
    if (!invoice?.id || exportingId.value === invoice.id) return;

    exportingId.value = invoice.id;

    try {
        const response = await axios({
            url: invoiceRoutes.generatePdf.url({ id: invoice.id }),
            method: 'GET',
            responseType: 'blob',
        });

        if (response.status === 204 || response.data.size === 0) {
            return;
        }

        const fallbackName = `invoice-${invoice.invoice_number ?? invoice.id}.pdf`;
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
    <Head title="Invoices" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Sales Tax Invoices</h1>
                <p class="text-sm text-muted-foreground">
                    Manage customer sales tax invoices, PO numbers, and GST billing.
                </p>
            </div>

            <Button variant="default" as-child>
                <Link :href="invoiceRoutes.create.url()">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Create Invoice
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
                            <th class="px-4 py-3">Invoice #</th>
                            <th class="px-4 py-3">Customer Name</th>
                            <th class="px-4 py-3">PO Number</th>
                            <th class="px-4 py-3">Pay Method</th>
                            <th class="px-4 py-3">Invoice Date</th>
                            <th class="px-4 py-3">Created By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr
                            v-for="(invoice, index) in invoices.data"
                            :key="invoice.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td class="px-4 py-3 font-medium text-muted-foreground">
                                {{ (invoices?.from ?? 1) + index }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ invoice?.invoice_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ invoice?.company?.name ?? '—' }}
                                <span v-if="invoice?.company?.branch">
                                    ({{ invoice.company.branch }})
                                </span>
                            </td>
                            <td class="px-4 py-3 text-foreground">
                                {{ invoice?.po_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ invoice?.pay_method || '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatDate(invoice?.date) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground"
                                >
                                    {{ invoice?.user?.name ?? 'System' }}
                                </span>
                            </td>
                            <td class="flex items-center justify-end gap-1 px-4 py-3 text-right">
                                <Link
                                    :href="invoiceRoutes?.edit?.url?.({ invoice: invoice.id })"
                                    title="Edit Invoice"
                                >
                                    <SquarePen class="h-4 w-4 text-blue-900" />
                                </Link>

                                <PreviewPdf
                                    :preview-url="invoiceRoutes.generatePdf.url({ id: invoice.id })"
                                    :download-url="invoiceRoutes.generatePdf.url({ id: invoice.id })"
                                    :filename="`invoice-${invoice.invoice_number ?? invoice.id}.pdf`"
                                    :title="`Invoice ${invoice.invoice_number ?? ''}`"
                                />

                                <Button
                                    variant="ghost"
                                    size="sm"
                                    :disabled="exportingId === invoice.id"
                                    :title="`Download PDF for ${invoice?.invoice_number ?? 'Invoice'}`"
                                    @click="exportToPdf(invoice)"
                                    class="cursor-pointer"
                                >
                                    <Loader2
                                        v-if="exportingId === invoice.id"
                                        class="mr-1.5 h-4 w-4 animate-spin"
                                    />
                                    <Download v-else class="h-4 w-4" />
                                </Button>

                                <Delete :id="invoice?.id" />
                            </td>
                        </tr>

                        <tr v-if="!invoices.data?.length">
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                        <FileText class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <p class="font-medium">No invoices found</p>
                                    <p class="text-xs text-muted-foreground">
                                        Get started by creating your first sales tax invoice.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="invoices.data?.length"
                :links="invoices.links"
                :from="invoices.from"
                :to="invoices.to"
                :total="invoices.total"
            />
        </Card>
    </div>
</template>
