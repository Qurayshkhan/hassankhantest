<script setup>
import { ref } from 'vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Button from '@/components/ui/button/Button.vue';
import { ArrowLeft, Plus, Save, Trash2 } from '@lucide/vue';
import DeliveryChallanController from '@/actions/App/Http/Controllers/DeliveryChallanController';
import deliveryChallanRoutes from '@/routes/delivery-challans';
import InputError from '@/components/InputError.vue';

import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatDisplayDate } from '@/lib/utils';

const props = defineProps({
    companies: {
        type: Array,
        default: () => [],
    },
    deliveryChallan: {
        type: Object,
        required: true,
    },
});

const selectedCompanyId = ref(props.deliveryChallan?.company_id ?? '');
const poNumber = ref(props.deliveryChallan?.po_number ?? '');
const deliveryDate = ref(props.deliveryChallan?.delivery_date ?? '');
const signatureText = ref(props.deliveryChallan?.signature_text ?? '');

const items = ref(
    props.deliveryChallan?.items?.length
        ? props.deliveryChallan.items.map((item) => ({
            key: `existing-${item.id}`,
            id: item.id,
            description: item.description ?? '',
            unit: item.unit ?? 'Each',
            quantity: item.quantity ?? '',
        }))
        : [createItem()]
);

function createItem() {
    return {
        key: `new-${Date.now()}-${Math.random()}`,
        id: null,
        description: '',
        unit: 'Each',
        quantity: '',
    };
}

function addItem() {
    items.value.push(createItem());
}

function removeItem(key) {
    if (items.value.length === 1) return;
    items.value = items.value.filter((item) => item.key !== key);
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Delivery Challans',
                href: '/delivery-challans',
            },
            {
                title: 'Edit',
                href: '#',
            },
        ],
    },
});
</script>

<template>

    <Head :title="`Edit Delivery Challan #${deliveryChallan.challan_number ?? deliveryChallan.id}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Card class="w-full">
            <CardHeader class="flex flex-row items-center justify-between gap-2">
                <div>
                    <CardTitle>Edit Delivery Challan</CardTitle>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Challan #{{ deliveryChallan.challan_number ?? deliveryChallan.id }}
                    </p>
                </div>

                <Button type="button" variant="outline" size="sm" as-child>
                    <Link
                        :href="deliveryChallanRoutes.index ? deliveryChallanRoutes.index.url() : '/delivery-challans'">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </CardHeader>

            <CardContent>
                <Form v-bind="DeliveryChallanController.update.form({ deliveryChallan: props.deliveryChallan.id })"
                    v-slot="{ errors, processing }">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <Label for="company_id">Customer</Label>
                            <Select id="company_id" name="company_id" v-model="selectedCompanyId">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a customer" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Customer Name</SelectLabel>
                                        <SelectItem v-for="company in props.companies" :key="company.id"
                                            :value="company.id">
                                            {{ `${company?.name} (${company?.branch})` }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="errors.company_id" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="po_number">PO Number</Label>
                            <Input id="po_number" name="po_number" v-model="poNumber" placeholder="Enter PO number" />
                            <InputError class="mt-2" :message="errors.po_number" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label for="date">Date</Label>
                            <VueDatePicker v-model="deliveryDate" uid="delivery_date" placeholder="dd/mm/yyyy"
                                :formats="{ input: 'dd/MM/yyyy' }" :time-config="{ enableTimePicker: false }"
                                :auto-apply="true" />
                            <input type="hidden" name="delivery_date" :value="formatDisplayDate(deliveryDate)" />
                            <InputError class="mt-2" :message="errors.delivery_date" />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Items</h2>
                            <p class="text-sm text-muted-foreground">Update, add, or remove items on this challan.</p>
                        </div>
                        <Button type="button" variant="outline" @click="addItem">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Item
                        </Button>
                    </div>

                    <InputError class="mt-2" :message="errors.items" />

                    <div class="mt-4 space-y-4">
                        <div v-for="(item, index) in items" :key="item.key"
                            class="grid gap-4 rounded-lg border p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                            <input v-if="item.id" type="hidden" :name="`items[${index}][id]`" :value="item.id" />

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-description-${item.key}`">Item Description</Label>
                                <Input :id="`item-description-${item.key}`" v-model="item.description"
                                    :name="`items[${index}][description]`" placeholder="Enter item description" />
                                <InputError class="mt-1" :message="errors[`items.${index}.description`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-unit-${item.key}`">Unit</Label>
                                <Select :id="`item-unit-${item.key}`" :name="`items[${index}][unit]`"
                                    v-model="item.unit">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a unit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Unit</SelectLabel>
                                            <SelectItem value="Each">Each</SelectItem>
                                            <SelectItem value="Pcs">Pcs</SelectItem>
                                            <SelectItem value="Kg">Kg</SelectItem>
                                            <SelectItem value="Box">Box</SelectItem>
                                            <SelectItem value="Meter">
                                                Meter
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-1" :message="errors[`items.${index}.unit`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-quantity-${item.key}`">Qty</Label>
                                <Input :id="`item-quantity-${item.key}`" v-model="item.quantity"
                                    :name="`items[${index}][quantity]`" type="number" min="0" step="any"
                                    placeholder="0" />
                                <InputError class="mt-1" :message="errors[`items.${index}.quantity`]" />
                            </div>

                            <div class="flex items-end justify-end">
                                <Button type="button" variant="outline" size="icon" :disabled="items.length === 1"
                                    :aria-label="`Remove item ${index + 1}`" @click="removeItem(item.key)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h2 class="text-lg font-semibold">Delivery Through (Signature)</h2>
                        <p class="mb-4 text-sm text-muted-foreground">Optional: enter a name.</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-1.5">
                                <Label for="signature_text">Signature Text</Label>
                                <Input id="signature_text" name="signature_text" v-model="signatureText"
                                    placeholder="Enter name or signature text" />
                                <InputError class="mt-2" :message="errors.signature_text" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <Button type="submit" variant="default" :disabled="processing">
                            <Save class="mr-2 h-4 w-4" />
                            {{ processing ? 'Updating...' : 'Update' }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
