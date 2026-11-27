<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import deliveryChallanRoutes from '@/routes/delivery-challans';
import Card from '@/components/ui/card/Card.vue';
import Button from '@/components/ui/button/Button.vue';
import Pagination from '@/components/Pagination.vue';
import { FileText, Plus, Download, Loader2, SquarePen } from '@lucide/vue';
import Delete from './Delete.vue';
import Filters from './Filters.vue'
import PreviewPdf from '@/components/PreviewPdf.vue';
defineProps({
    deliveryChallans: {
        type: Object,
        required: true,
    },
    filters:Object
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Delivery Challans',
                href: '/delivery-challans',
            },
        ],
    },
});

// Track the specific challan ID currently downloading
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

const exportToPdf = async (challan) => {
    if (!challan?.id || exportingId.value === challan.id) return;

    exportingId.value = challan.id;

    try {
        const response = await axios({
            url: deliveryChallanRoutes.download.url({ id: challan.id }),
            method: 'GET',
            responseType: 'blob',
        });

        // Guard against IDM / download manager 204 interceptions
        if (response.status === 204 || response.data.size === 0) {
            return;
        }

        const fallbackName = `delivery-challan-${challan.po_number ?? challan.id}.pdf`;
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

    <Head title="Delivery Challans" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Delivery Challans</h1>
                <p class="text-sm text-muted-foreground">
                    Manage customer delivery challans and dispatch records.
                </p>
            </div>

            <Button variant="default" as-child>
                <Link :href="deliveryChallanRoutes.create.url()">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Create Delivery Challan
                </Link>
            </Button>
        </div>

        <Card class="overflow-hidden p-0">
            <Filters :filters="filters"/>
            <div class="overflow-x-auto">
                <table class="w-full caption-bottom border-collapse text-left text-sm">
                    <thead
                        class="border-b bg-muted/50 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Challan Number</th>
                            <th class="px-4 py-3">PO Number</th>
                            <th class="px-4 py-3">Customer Name</th>
                            <th class="px-4 py-3">Delivery Date</th>
                            <th class="px-4 py-3">Created By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(deliveryChallan, index) in deliveryChallans.data" :key="deliveryChallan.id"
                            class="transition-colors hover:bg-muted/40">
                            <td class="px-4 py-3 font-medium text-muted-foreground">
                                {{ (deliveryChallans?.from ?? 1) + index }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ deliveryChallan?.challan_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ deliveryChallan?.po_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ deliveryChallan?.company?.name ?? '—' }} ({{ deliveryChallan?.company?.branch }})
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatDate(deliveryChallan?.delivery_date) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground">
                                    {{ deliveryChallan?.user?.name ?? 'System' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right flex items-center justify-end gap-1">
                                <Link :href="deliveryChallanRoutes?.edit?.url?.({ deliveryChallan: deliveryChallan.id })
                                    " title="Edit Delivery Challan">
                                    <SquarePen class="h-4 w-4 text-blue-900" />
                                </Link>
                                <PreviewPdf
                                    :preview-url="deliveryChallanRoutes.download.url({ id: deliveryChallan.id })"
                                    :download-url="deliveryChallanRoutes.download.url({ id: deliveryChallan.id })"
                                    :filename="`delivery-challan-${deliveryChallan.challan_number ?? deliveryChallan.po_number ?? deliveryChallan.id}.pdf`"
                                    :title="`Delivery Challan ${deliveryChallan.challan_number ?? ''}`"
                                />
                                <Button variant="ghost" size="sm" :disabled="exportingId === deliveryChallan.id"
                                    :title="`Download PDF for ${deliveryChallan?.po_number ?? 'Challan'}`"
                                    @click="exportToPdf(deliveryChallan)" class="cursor-pointer">
                                    <Loader2 v-if="exportingId === deliveryChallan.id"
                                        class="mr-1.5 h-4 w-4 animate-spin" />
                                    <Download v-else class="h-4 w-4" />
                                </Button>
                               <Delete :id="deliveryChallan?.id"/>
                            </td>
                        </tr>

                        <tr v-if="!deliveryChallans.data?.length">
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                        <FileText class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <p class="font-medium">No delivery challans found</p>
                                    <p class="text-xs text-muted-foreground">
                                        Get started by creating your first delivery challan.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination v-if="deliveryChallans.data?.length" :links="deliveryChallans.links"
                :from="deliveryChallans.from" :to="deliveryChallans.to" :total="deliveryChallans.total" />
        </Card>
    </div>
</template>
