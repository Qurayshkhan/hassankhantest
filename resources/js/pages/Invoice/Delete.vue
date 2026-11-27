<script setup>
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Modal from '@/components/Modal.vue';
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import { Trash2 } from '@lucide/vue';

const props = defineProps({
    id: {
        type: [Number, String],
        required: true,
    },
});

const confirmingRecordDeletion = ref(false);

const confirmRecordDeletion = () => {
    confirmingRecordDeletion.value = true;
};

const closeModal = () => {
    confirmingRecordDeletion.value = false;
};
</script>

<template>
    <div class="inline-flex">
        <Button
            type="button"
            variant="ghost"
            size="sm"
            title="Delete Invoice"
            class="cursor-pointer text-destructive hover:bg-destructive/10 hover:text-destructive"
            @click="confirmRecordDeletion"
        >
            <Trash2 class="h-4 w-4" />
        </Button>

        <Modal :show="confirmingRecordDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground">
                    Are you sure you want to delete this invoice?
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    This action cannot be undone. All associated items for this invoice will be removed.
                </p>

                <Form
                    v-bind="InvoiceController.destroy.form({ invoice: props.id })"
                    reset-on-success
                    :options="{ preserveScroll: true }"
                    @success="closeModal"
                    v-slot="{ processing }"
                >
                    <div class="mt-6 flex justify-end gap-3">
                        <Button
                            type="button"
                            variant="secondary"
                            :disabled="processing"
                            @click="closeModal"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            variant="destructive"
                            :disabled="processing"
                            data-test="confirm-delete-invoice-button"
                        >
                            {{ processing ? 'Deleting...' : 'Delete' }}
                        </Button>
                    </div>
                </Form>
            </div>
        </Modal>
    </div>
</template>
