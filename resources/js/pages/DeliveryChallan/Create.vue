<script setup>
import { ref } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Button from '@/components/ui/button/Button.vue';
import { Plus, Trash2 } from '@lucide/vue';
import DeliveryChallanController from '@/actions/App/Http/Controllers/DeliveryChallanController';
import InputError from '@/components/InputError.vue';

import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { VueDatePicker } from '@vuepic/vue-datepicker';
import { formatDisplayDate } from '@/lib/utils';

const props = defineProps({
    companies: Object
})
const items = ref([createItem()]);
const date = ref('');
function createItem() {
    return {
        id: `${Date.now()}-${Math.random()}`,
        description: '',
        unit: 'Each',
        quantity: '',
    };
}

function addItem() {
    items.value.push(createItem());
}

function removeItem(id) {
    items.value = items.value.filter((item) => item.id !== id);
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Delivery Challans',
                href: '/delivery-challans',
            },
            {
                title: 'Create',
                href: '/delivery-challans/create',
            },
        ],
    },
});
</script>

<template>

    <Head title="Create Delivery Challan" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Card class="w-full">
            <CardHeader>
                <CardTitle>Create Delivery Challan</CardTitle>
            </CardHeader>

            <CardContent>
                <Form v-bind="DeliveryChallanController.store.form()" v-slot="{ errors, processing }">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <Label for="po_number">Customer</Label>
                            <Select name="company_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a customer" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Customer Name</SelectLabel>
                                        <SelectItem :value="company.id" v-for="company in props.companies">
                                            {{ `${company?.name} (${company?.branch})` }}
                                        </SelectItem>

                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="errors.company_id" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label for="po_number">PO Number</Label>
                            <Input id="po_number" name="po_number" placeholder="Enter PO number" />
                            <InputError class="mt-2" :message="errors.po_number" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="date">Date</Label>
                            <VueDatePicker v-model="date" uid="delivery_date" placeholder="dd/mm/yyyy"
                                :formats="{ input: 'dd/MM/yyyy' }" :time-config="{ enableTimePicker: false }"
                                :auto-apply="true" />
                            <input type="hidden" name="delivery_date" :value="formatDisplayDate(date)" />
                            <InputError class="mt-2" :message="errors.delivery_date" />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Items</h2>
                            <p class="text-sm text-muted-foreground">Add one or more items to this challan.</p>
                        </div>
                        <Button type="button" variant="outline" @click="addItem">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Item
                        </Button>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div v-for="(item, index) in items" :key="item.id"
                            class="grid gap-4 rounded-lg border p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-description-${item.id}`">Item Description</Label>
                                <Input :id="`item-description-${item.id}`" v-model="item.description"
                                    :name="`items[${index}][description]`" placeholder="Enter item description" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-unit-${item.id}`">Unit</Label>
                                <Select :name="`items[${index}][unit]`" :id="`item-unit-${item.id}`" v-model="item.unit"
                                    default-value="Each">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a unit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Unit</SelectLabel>
                                            <SelectItem value="Each">
                                                Each
                                            </SelectItem>
                                            <SelectItem value="Pcs">
                                                Pcs
                                            </SelectItem>
                                            <SelectItem value="Kg">
                                                Kg
                                            </SelectItem>
                                            <SelectItem value="Box">
                                                Box
                                            </SelectItem>
                                            <SelectItem value="Meter">
                                                Meter
                                            </SelectItem>

                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-quantity-${item.id}`">Qty</Label>
                                <Input :id="`item-quantity-${item.id}`" v-model="item.quantity"
                                    :name="`items[${index}][quantity]`" type="number" min="0" step="any"
                                    placeholder="0" />
                            </div>
                            <div class="flex items-end justify-end">
                                <Button type="button" variant="outline" size="icon"
                                    :aria-label="`Remove item ${index + 1}`" @click="removeItem(item.id)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h2 class="text-lg font-semibold">Delivery Through (Signature)</h2>
                        <p class="mb-4 text-sm text-muted-foreground">Optional: enter a name.
                        </p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-1.5">
                                <Label for="signature_text">Signature Text</Label>
                                <Input id="signature_text" name="signature_text"
                                    placeholder="Enter name or signature text" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label for="signature_file">Signature File</Label>
                                <Input id="signature_file" name="signature_file" type="file" accept="image/*,.pdf" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <Button type="submit" variant="default" :disabled="processing">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ processing ? 'Saving...' : 'Create Delivery Challan' }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
