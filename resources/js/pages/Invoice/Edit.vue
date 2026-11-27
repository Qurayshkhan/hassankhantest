<script setup>
import { computed, ref } from 'vue';
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
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import invoiceRoutes from '@/routes/invoices';
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
import { VueDatePicker } from '@vuepic/vue-datepicker';
import { formatDisplayDate } from '@/lib/utils';

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
    companies: {
        type: [Array, Object],
        required: true,
    },
});

function formatDateForInput(dateString) {
    if (!dateString) return '';
    return String(dateString).slice(0, 10);
}

function createItem(existing = null) {
    if (existing) {
        return {
            key: `existing-${existing.id}`,
            dbId: existing.id,
            description: existing.description ?? '',
            unit: existing.unit ?? 'Each',
            quantity: existing.quantity ?? '',
            price_per_unit: existing.price_per_unit ?? '',
        };
    }

    return {
        key: `new-${Date.now()}-${Math.random()}`,
        dbId: null,
        description: '',
        unit: 'Each',
        quantity: '',
        price_per_unit: '',
    };
}

const selectedCompanyId = ref(props.invoice.company_id ?? null);
const invoiceDate = ref(formatDateForInput(props.invoice.date));
const poNumber = ref(props.invoice.po_number ?? '');
const payMethod = ref(props.invoice.pay_method ?? '');
const signatureText = ref(props.invoice.signature_text ?? '');


const items = ref(
    props.invoice.items?.length
        ? props.invoice.items.map((item) => createItem(item))
        : [createItem()]
);

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

function removeItem(key) {
    if (items.value.length === 1) {
        items.value = [createItem()];
        return;
    }
    items.value = items.value.filter((item) => item.key !== key);
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Invoices', href: '/invoices' },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>

    <Head :title="`Edit Invoice ${invoice.invoice_number ?? ''}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Card class="w-full">
            <CardHeader class="flex flex-row items-center justify-between gap-2">
                <div>

                    <CardTitle>
                        Edit Invoice
                        <span v-if="invoice.invoice_number" class="ml-2 text-sm font-normal text-muted-foreground">
                            (#{{ invoice.invoice_number }})
                        </span>
                    </CardTitle>

                </div>
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="invoiceRoutes.index ? invoiceRoutes.index.url() : '/invoices'">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </CardHeader>

            <CardContent>
                <Form v-bind="InvoiceController.update.form({ invoice: invoice.id })" v-slot="{ errors, processing }">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="flex flex-col gap-1.5">
                            <Label for="company_id">Customer</Label>
                            <Select v-model="selectedCompanyId" name="company_id">
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
                            <VueDatePicker v-model="invoiceDate" uid="date" placeholder="dd/mm/yyyy"
                                :formats="{ input: 'dd/MM/yyyy' }" :time-config="{ enableTimePicker: false }"
                                :auto-apply="true" />
                            <input type="hidden" name="date" :value="formatDisplayDate(invoiceDate)" />
                            <InputError class="mt-2" :message="errors.date" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="po_number">PO Number</Label>
                            <Input id="po_number" v-model="poNumber" name="po_number" type="number" />
                            <InputError class="mt-2" :message="errors.po_number" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="pay_method">Pay Method</Label>
                            <!-- <Input id="pay_method" v-model="payMethod" name="pay_method" /> -->

                            <Select id="pay_method" v-model="payMethod" name="pay_method">
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
                                Update, add, or remove items on this invoice.
                            </p>
                        </div>
                        <Button type="button" variant="outline" @click="addItem">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Item
                        </Button>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div v-for="(item, index) in items" :key="item.key"
                            class="grid gap-4 rounded-lg border p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,0.9fr)_minmax(0,0.9fr)_minmax(0,1fr)_minmax(0,1.1fr)_minmax(0,1fr)_minmax(0,1.1fr)_auto]">
                            <input v-if="item.dbId" type="hidden" :name="`items[${index}][id]`" :value="item.dbId" />

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-description-${item.key}`">Description</Label>
                                <Input :id="`item-description-${item.key}`" v-model="item.description"
                                    :name="`items[${index}][description]`" />
                                <InputError class="mt-1" :message="errors[`items.${index}.description`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-unit-${item.key}`">Unit</Label>
                                <Select :id="`item-unit-${item.key}`" v-model="item.unit"
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
                                <Label :for="`item-quantity-${item.key}`">Qty</Label>
                                <Input :id="`item-quantity-${item.key}`" v-model="item.quantity"
                                    :name="`items[${index}][quantity]`" type="number" min="0" step="any" />
                                <InputError class="mt-1" :message="errors[`items.${index}.quantity`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-rate-${item.key}`">Rate / Unit</Label>
                                <Input :id="`item-rate-${item.key}`" v-model="item.price_per_unit"
                                    :name="`items[${index}][price_per_unit]`" type="number" min="0" step="any" />
                                <InputError class="mt-1" :message="errors[`items.${index}.price_per_unit`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-excl-tax-${item.key}`">Excl. Tax</Label>
                                <Input :id="`item-excl-tax-${item.key}`" :model-value="getExclTax(item).toFixed(2)"
                                    :name="`items[${index}][amount_excluding_sale_tax]`" type="number" step="any"
                                    readonly class="bg-muted/50" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-gst-${item.key}`">GST 18%</Label>
                                <Input :id="`item-gst-${item.key}`" :model-value="getGst18(item).toFixed(2)"
                                    :name="`items[${index}][plus_gs_t18]`" type="number" step="any" readonly
                                    class="bg-muted/50" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-line-total-${item.key}`">Line Total</Label>
                                <Input :id="`item-line-total-${item.key}`" :model-value="getLineTotal(item).toFixed(2)"
                                    :name="`items[${index}][line_total]`" type="number" step="any" readonly
                                    class="bg-muted/50 font-medium" />
                            </div>

                            <div class="flex items-end justify-end">
                                <Button type="button" variant="outline" size="icon"
                                    :aria-label="`Remove item ${index + 1}`" @click="removeItem(item.key)">
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
                        <p class="mb-4 text-sm text-muted-foreground">
                            Optional signature text or upload a replacement file.
                        </p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-1.5">
                                <Label for="signature_text">Signature Text</Label>
                                <Input id="signature_text" v-model="signatureText" name="signature_text" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label for="signature_file">Signature File</Label>
                                <Input id="signature_file" name="signature_file" type="file" accept="image/*,.pdf" />
                                <span v-if="invoice.signature_file" class="text-xs text-muted-foreground">
                                    Current: {{ invoice.signature_file }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">

                        <Button type="submit" variant="default" :disabled="processing">
                            <Save class="mr-2 h-4 w-4" />
                            {{ processing ? 'Updating...' : 'Update Invoice' }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
