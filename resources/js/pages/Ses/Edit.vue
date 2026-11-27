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
import SesController from '@/actions/App/Http/Controllers/SesController';
import sesRoutes from '@/routes/ses';
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

const props = defineProps({
    ses: {
        type: Object,
        required: true,
    },
    companies: {
        type: [Array, Object],
        required: true,
    },
});

function parseInitialDate(dateString) {
    if (!dateString) return '';

    const str = String(dateString).trim();

    // Handle YYYY-MM-DD or ISO timestamps safely in local time
    const isoMatch = str.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (isoMatch) {
        const [, year, month, day] = isoMatch;
        return new Date(Number(year), Number(month) - 1, Number(day));
    }

    // Handle dd/MM/yyyy or dd-MM-yyyy
    const dmyMatch = str.match(/^(\d{2})[/-](\d{2})[/-](\d{4})$/);
    if (dmyMatch) {
        const [, day, month, year] = dmyMatch;
        return new Date(Number(year), Number(month) - 1, Number(day));
    }

    const parsed = new Date(str);
    return Number.isNaN(parsed.getTime()) ? '' : parsed;
}

function formatDisplayDate(val) {
    if (!val) return '';

    if (typeof val === 'string' && /^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
        return val;
    }

    const d = val instanceof Date ? val : parseInitialDate(val);
    if (!(d instanceof Date) || Number.isNaN(d.getTime())) return String(val);

    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return `${day}/${month}/${year}`;
}

function inferRate(existing) {
    if (existing.rate !== undefined && existing.rate !== null && existing.rate !== '') {
        return existing.rate;
    }
    const qty = parseFloat(existing.quantity);
    const amt = parseFloat(existing.amount);
    if (!Number.isNaN(qty) && qty > 0 && !Number.isNaN(amt)) {
        return Number((amt / qty).toFixed(4));
    }
    return '';
}

function createItem(existing = null) {
    if (existing) {
        return {
            key: `existing-${existing.id}`,
            dbId: existing.id,
            description: existing.description ?? '',
            unit: existing.unit ?? 'Each',
            quantity: existing.quantity ?? '',
            rate: inferRate(existing),
        };
    }

    return {
        key: `new-${Date.now()}-${Math.random()}`,
        dbId: null,
        description: '',
        unit: 'Each',
        quantity: '',
        rate: '',
    };
}

const selectedCompanyId = ref(props.ses.company_id ?? null);
const date = ref(parseInitialDate(props.ses.date));
const poNumber = ref(props.ses.po_number ?? '');
const rgp = ref(props.ses.rgp ?? '');
const igp = ref(props.ses.igp ?? '');
const signatureText = ref(props.ses.signature_text ?? '');

const items = ref(
    props.ses.items?.length
        ? props.ses.items.map((item) => createItem(item))
        : [createItem()]
);

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

function getItemAmount(item) {
    const qty = parseFloat(item.quantity);
    const rate = parseFloat(item.rate);
    if (Number.isNaN(qty) || Number.isNaN(rate)) {
        return 0;
    }
    return Math.max(0, qty * rate);
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

const totalQuantity = computed(() =>
    items.value.reduce((sum, item) => {
        const qty = parseFloat(item.quantity);
        return sum + (Number.isNaN(qty) ? 0 : Math.max(0, qty));
    }, 0),
);

const grandTotal = computed(() =>
    items.value.reduce((sum, item) => sum + getItemAmount(item), 0),
);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'SES', href: '/ses' },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit SES ${ses.document_number ?? ''}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Card class="w-full">
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle>
                        Edit SES
                        <span v-if="ses.document_number" class="ml-2 text-sm font-normal text-muted-foreground">
                            (#{{ ses.document_number }})
                        </span>
                    </CardTitle>
                </div>
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="sesRoutes.index ? sesRoutes.index.url() : '/ses'">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </CardHeader>

            <CardContent>
                <Form v-bind="SesController.update.form({ ses: ses.id })" v-slot="{ errors, processing }">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="flex flex-col gap-1.5">
                            <Label for="company_id">Customer</Label>
                            <Select v-model="selectedCompanyId" name="company_id">
                                <SelectTrigger id="company_id" class="w-full">
                                    <SelectValue placeholder="Select a customer" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Customer Name</SelectLabel>
                                        <SelectItem
                                            v-for="company in props.companies"
                                            :key="company.id"
                                            :value="company.id"
                                        >
                                            {{ `${company?.name}${company?.branch ? ` (${company.branch})` : ''}` }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="errors.company_id" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="date">Date</Label>
                            <VueDatePicker
                                v-model="date"
                                placeholder="dd/mm/yyyy"
                                :formats="{ input: 'dd/MM/yyyy' }"
                                :time-config="{ enableTimePicker: false }"
                                :auto-apply="true"
                            />
                            <input type="hidden" name="date" :value="formatDisplayDate(date)" />
                            <InputError class="mt-2" :message="errors.date" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="po_number">PO Number</Label>
                            <Input id="po_number" v-model="poNumber" name="po_number" type="number" placeholder="Enter PO number" />
                            <InputError class="mt-2" :message="errors.po_number" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="rgp">RGP</Label>
                            <Input id="rgp" v-model="rgp" name="rgp" placeholder="Enter RGP" />
                            <InputError class="mt-2" :message="errors.rgp" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="igp">IGP</Label>
                            <Input id="igp" v-model="igp" name="igp" placeholder="Enter IGP" />
                            <InputError class="mt-2" :message="errors.igp" />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Items</h2>
                            <p class="text-sm text-muted-foreground">Update, add, or remove items on this SES.</p>
                        </div>
                        <Button type="button" variant="outline" @click="addItem">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Item
                        </Button>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div
                            v-for="(item, index) in items"
                            :key="item.key"
                            class="grid gap-4 rounded-lg border p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,0.9fr)_minmax(0,0.9fr)_minmax(0,1fr)_minmax(0,1.1fr)_auto]"
                        >
                            <input
                                v-if="item.dbId"
                                type="hidden"
                                :name="`items[${index}][id]`"
                                :value="item.dbId"
                            />

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-description-${item.key}`">Description</Label>
                                <Input
                                    :id="`item-description-${item.key}`"
                                    v-model="item.description"
                                    :name="`items[${index}][description]`"
                                    placeholder="Enter item description"
                                />
                                <InputError class="mt-1" :message="errors[`items.${index}.description`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-unit-${item.key}`">Unit</Label>
                                <Select :id="`item-unit-${item.key}`" v-model="item.unit" :name="`items[${index}][unit]`">
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
                                            <SelectItem value="Meter">Meter</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-quantity-${item.key}`">Quantity</Label>
                                <Input
                                    :id="`item-quantity-${item.key}`"
                                    v-model="item.quantity"
                                    :name="`items[${index}][quantity]`"
                                    type="number"
                                    min="0"
                                    step="any"
                                    placeholder="0"
                                />
                                <InputError class="mt-1" :message="errors[`items.${index}.quantity`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-rate-${item.key}`">Rate</Label>
                                <Input
                                    :id="`item-rate-${item.key}`"
                                    v-model="item.rate"
                                    :name="`items[${index}][rate]`"
                                    type="number"
                                    min="0"
                                    step="any"
                                    placeholder="0.00"
                                />
                                <InputError class="mt-1" :message="errors[`items.${index}.rate`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-amount-${item.key}`">Amount</Label>
                                <Input
                                    :id="`item-amount-${item.key}`"
                                    :model-value="formatNumber(getItemAmount(item))"
                                    type="text"
                                    readonly
                                    class="bg-muted/50 font-medium"
                                />
                                <input
                                    type="hidden"
                                    :name="`items[${index}][amount]`"
                                    :value="getItemAmount(item).toFixed(2)"
                                />
                                <InputError class="mt-1" :message="errors[`items.${index}.amount`]" />
                            </div>

                            <div class="flex items-end justify-end">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :aria-label="`Remove item ${index + 1}`"
                                    @click="removeItem(item.key)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Preview -->
                    <div class="mt-6 rounded-lg border bg-muted/30 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                Calculation Preview
                            </h3>
                            <span class="text-xs text-muted-foreground">
                                {{ items.length }} {{ items.length === 1 ? 'line item' : 'line items' }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div
                                v-for="(item, index) in items"
                                :key="`preview-${item.key}`"
                                class="flex flex-wrap items-center justify-between gap-2 border-b border-dashed pb-2 last:border-b-0 last:pb-0"
                            >
                                <div class="text-muted-foreground">
                                    <span class="font-medium text-foreground">
                                        {{ item.description || `Item #${index + 1}` }}
                                    </span>
                                    <span class="ml-2">
                                        ({{ parseFloat(item.quantity) || 0 }} {{ item.unit }} × {{ formatNumber(item.rate) }})
                                    </span>
                                </div>
                                <div class="font-medium tabular-nums">
                                    {{ formatNumber(getItemAmount(item)) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-4 border-t pt-3">
                            <div class="text-sm text-muted-foreground">
                                Total Quantity: <span class="font-semibold text-foreground">{{ totalQuantity }}</span>
                            </div>
                            <div class="text-base font-semibold">
                                Grand Total: <span class="tabular-nums">{{ formatNumber(grandTotal) }}</span>
                            </div>
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
                                <Input id="signature_text" v-model="signatureText" name="signature_text" placeholder="Enter name or signature text" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label for="signature_file">Signature File</Label>
                                <Input id="signature_file" name="signature_file" type="file" accept="image/*,.pdf" />
                                <span v-if="ses.signature_file" class="text-xs text-muted-foreground">
                                    Current: {{ ses.signature_file }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <Button type="submit" variant="default" :disabled="processing">
                            <Save class="mr-2 h-4 w-4" />
                            {{ processing ? 'Updating...' : 'Update SES' }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
