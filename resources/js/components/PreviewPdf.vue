<script setup>
import { onBeforeUnmount, ref } from 'vue';
import axios from 'axios';
import Button from '@/components/ui/button/Button.vue';
import Modal from '@/components/Modal.vue';
import { Eye, Download, Loader2, X, Send } from '@lucide/vue';

const props = defineProps({
    previewUrl: {
        type: String,
        required: true,
    },
    downloadUrl: {
        type: String,
        default: '',
    },
    filename: {
        type: String,
        default: 'document.pdf',
    },
    title: {
        type: String,
        default: 'PDF Preview',
    },
});

const loadingPreview = ref(false);
const downloading = ref(false);
const showModal = ref(false);
const objectUrl = ref('');

function clearObjectUrl() {
    if (objectUrl.value) {
        window.URL.revokeObjectURL(objectUrl.value);
        objectUrl.value = '';
    }
}

function closeModal() {
    showModal.value = false;
    clearObjectUrl();
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

function appendPreviewQuery(url) {
    const separator = url.includes('?') ? '&' : '?';
    return `${url}${separator}preview=1`;
}

const openPreview = async () => {
    if (!props.previewUrl || loadingPreview.value) return;

    loadingPreview.value = true;
    clearObjectUrl();

    try {
        const response = await axios({
            url: appendPreviewQuery(props.previewUrl),
            method: 'GET',
            responseType: 'blob',
        });

        if (response.status === 204 || response.data.size === 0) {
            return;
        }

        const blob = new Blob([response.data], { type: 'application/pdf' });
        objectUrl.value = window.URL.createObjectURL(blob);
        showModal.value = true;
    } catch (error) {
        console.error('PDF preview failed:', error);
    } finally {
        loadingPreview.value = false;
    }
};

const handleDownload = async () => {
    const targetUrl = props.downloadUrl || props.previewUrl;
    if (!targetUrl || downloading.value) return;

    downloading.value = true;

    try {
        const response = await axios({
            url: targetUrl,
            method: 'GET',
            responseType: 'blob',
        });

        if (response.status === 204 || response.data.size === 0) {
            return;
        }

        const resolvedName = getFilenameFromHeader(
            response.headers['content-disposition'],
            props.filename
        );

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', resolvedName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('PDF download failed:', error);
    } finally {
        downloading.value = false;
    }
};

onBeforeUnmount(() => {
    clearObjectUrl();
});

const shareViaGmail = (fileName) => {
    // const docNumber = invoice?.invoice_number ?? invoice?.id ?? '';
    // const customerName = invoice?.company?.name ?? 'Customer';
    // const invoiceDate = formatDate(invoice?.date);

    const subject = fileName;

    openGmailCompose({ subject });
};

function openGmailCompose({ subject = '', body = '', to = '' }) {
    const gmailUrl = new URL('https://mail.google.com/mail/');
    gmailUrl.searchParams.set('view', 'cm');
    gmailUrl.searchParams.set('fs', '1');
    if (to) gmailUrl.searchParams.set('to', to);
    if (subject) gmailUrl.searchParams.set('su', subject);
    if (body) gmailUrl.searchParams.set('body', body);

    window.open(gmailUrl.toString(), '_blank', 'noopener,noreferrer');
}
</script>

<template>
    <div class="inline-flex">
        <Button type="button" variant="ghost" size="sm" :disabled="loadingPreview" :title="`Preview ${title}`"
            class="cursor-pointer" @click="openPreview">
            <Loader2 v-if="loadingPreview" class="h-4 w-4 animate-spin" />
            <Eye v-else class="h-4 w-4" />
        </Button>

        <Modal :show="showModal" maxWidth="7xl" @close="closeModal">
            <div class="flex h-[85vh] flex-col p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-medium text-foreground">
                            {{ title }}
                        </h2>
                        <p class="truncate text-sm text-muted-foreground">
                            {{ filename }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            size="sm"
                            :disabled="downloading"
                            class="cursor-pointer"
                            @click="handleDownload"
                            variant="ghost"
                        >
                            <Loader2 v-if="downloading" class="animate-spin" />
                            <Download v-else  />

                        </Button>

                        <Button type="button" variant="ghost" size="sm" title="Share via Gmail" class="cursor-pointer"
                            @click="shareViaGmail(filename)">
                            <Send class="h-4 w-4" />
                        </Button>

                        <Button type="button" variant="ghost" size="icon" aria-label="Close PDF preview"
                            class="cursor-pointer" @click="closeModal">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-hidden rounded-md border bg-muted/30">
                    <iframe v-if="objectUrl" :src="objectUrl" :title="title" class="h-full w-full border-0" />
                </div>
            </div>
        </Modal>
    </div>
</template>
