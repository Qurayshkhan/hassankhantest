<script setup>
import { computed, ref } from 'vue';
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
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
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
import { VueDatePicker } from '@vuepic/vue-datepicker';

const props = defineProps({
    companies: {
        type: [Array, Object],
        required: true,
    },
});

const items = ref([createItem()]);
const date = ref('');

function createItem() {
    return {
        id: `${Date.now()}-${Math.random()}`,
        description: '',
        unit: 'Each',
        quantity: '',
        price_per_unit: '',
    };
}

function getExclTax(item) {
    const qty = parseFloat(item.quantity);
    const rate = parseFloat(item.price_per_unit);
    if (Number.isNaN(qty) || Number.isNaN(rate)) return 0;
    return +(qty * rate).toFixed(2);
}

function getGst18(item) {
    return +(getExclTax(item) * 0.18).toFixed(2);
}

function getLineTotal(item) {
    return +(getExclTax(item) + getGst18(item)).toFixed(2);
}

const totals = computed(() => {
    const excl = items.value.reduce((s, i) => s + getExclTax(i), 0);
    const gst = items.value.reduce((s, i) => s + getGst18(i), 0);
    const total = items.value.reduce((s, i) => s + getLineTotal(i), 0);
    return {
        excl: +excl.toFixed(2),
        gst: +gst.toFixed(2),
        total: +total.toFixed(2),
    };
});

function addItem() {
    items.value.push(createItem());
}

function removeItem(id) {
    if (items.value.length === 1) {
        items.value = [createItem()];
        return;
    }
    items.value = items.value.filter((item) => item.id !== id);
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Invoices', href: '/invoices' },
            { title: 'Create', href: '/invoices/create' },
        ],
    },
});
</script>

<template>

    <Head title="Create Invoice" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Card class="w-full">
            <CardHeader>
                <CardTitle>Create Sales Tax Invoice</CardTitle>
            </CardHeader>

            <CardContent>
                <Form v-bind="InvoiceController.store.form()" v-slot="{ errors, processing }">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="flex flex-col gap-1.5">
                            <Label for="company_id">Customer</Label>
                            <Select name="company_id">
                                <SelectTrigger id="company_id" class="w-full">
                                    <SelectValue placeholder="Select a customer" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Customer Name</SelectLabel>
                                        <SelectItem v-for="company in props.companies" :key="company.id"
                                            :value="company.id">
                                            {{ `${company?.name}${company?.branch ? ` (${company.branch})` : ''}` }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="errors.company_id" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="date">Date</Label>
                            <VueDatePicker v-model="date" uid="date" placeholder="dd/mm/yyyy"
                                :formats="{ input: 'dd/MM/yyyy' }" :time-config="{ enableTimePicker: false }"
                                :auto-apply="true" />
                            <input type="hidden" name="date" :value="formatDisplayDate(date)" />
                            <InputError class="mt-2" :message="errors.date" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="po_number">PO Number</Label>
                            <Input id="po_number" name="po_number" type="number" placeholder="Enter PO number" />
                            <InputError class="mt-2" :message="errors.po_number" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="pay_method">Pay Method</Label>
                            <Select id="pay_method" name="pay_method" default-value="Cheque">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Payment Method" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Payment Method</SelectLabel>
                                        <SelectItem value="Cheque">Cheque</SelectItem>
                                        <SelectItem value="Bank">Bank Transfer</SelectItem>
                                        <SelectItem value="Cash">Cash</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="errors.pay_method" />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Items</h2>
                            <p class="text-sm text-muted-foreground">
                                GST 18% and line totals calculate automatically.
                            </p>
                        </div>
                        <Button type="button" variant="outline" @click="addItem">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Item
                        </Button>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div v-for="(item, index) in items" :key="item.id"
                            class="grid gap-4 rounded-lg border p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,0.9fr)_minmax(0,0.9fr)_minmax(0,1fr)_minmax(0,1.1fr)_minmax(0,1fr)_minmax(0,1.1fr)_auto]">
                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-description-${item.id}`">Description</Label>
                                <Input :id="`item-description-${item.id}`" v-model="item.description"
                                    :name="`items[${index}][description]`" placeholder="Item description" />
                                <InputError class="mt-1" :message="errors[`items.${index}.description`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-unit-${item.id}`">Unit</Label>
                                <Select :id="`item-unit-${item.id}`" v-model="item.unit"
                                    :name="`items[${index}][unit]`">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Unit" />
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
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-quantity-${item.id}`">Qty</Label>
                                <Input :id="`item-quantity-${item.id}`" v-model="item.quantity"
                                    :name="`items[${index}][quantity]`" type="number" min="0" step="any"
                                    placeholder="0" />
                                <InputError class="mt-1" :message="errors[`items.${index}.quantity`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-rate-${item.id}`">Rate / Unit</Label>
                                <Input :id="`item-rate-${item.id}`" v-model="item.price_per_unit"
                                    :name="`items[${index}][price_per_unit]`" type="number" min="0" step="any"
                                    placeholder="0.00" />
                                <InputError class="mt-1" :message="errors[`items.${index}.price_per_unit`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-excl-tax-${item.id}`">Excl. Tax</Label>
                                <Input :id="`item-excl-tax-${item.id}`" :model-value="getExclTax(item).toFixed(2)"
                                    :name="`items[${index}][amount_excluding_sale_tax]`" type="number" step="any"
                                    readonly class="bg-muted/50" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-gst-${item.id}`">GST 18%</Label>
                                <Input :id="`item-gst-${item.id}`" :model-value="getGst18(item).toFixed(2)"
                                    :name="`items[${index}][plus_gs_t18]`" type="number" step="any" readonly
                                    class="bg-muted/50" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-line-total-${item.id}`">Line Total</Label>
                                <Input :id="`item-line-total-${item.id}`" :model-value="getLineTotal(item).toFixed(2)"
                                    :name="`items[${index}][line_total]`" type="number" step="any" readonly
                                    class="bg-muted/50 font-medium" />
                            </div>

                            <div class="flex items-end justify-end">
                                <Button type="button" variant="outline" size="icon"
                                    :aria-label="`Remove item ${index + 1}`" @click="removeItem(item.id)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-end gap-6 border-t pt-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Excl. Tax: </span>
                            <span class="font-semibold">{{ totals.excl.toFixed(2) }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Total GST (18%): </span>
                            <span class="font-semibold">{{ totals.gst.toFixed(2) }}</span>
                        </div>
                        <div class="text-base">
                            <span class="text-muted-foreground">Payment Due: </span>
                            <span class="font-bold">{{ totals.total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h2 class="text-lg font-semibold">Signature</h2>
                        <p class="mb-4 text-sm text-muted-foreground">Optional signature text or file.</p>
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
                            {{ processing ? 'Saving...' : 'Create Invoice' }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
