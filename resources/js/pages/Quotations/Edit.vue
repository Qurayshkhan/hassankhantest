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
import QuotationController from '@/actions/App/Http/Controllers/QuotationController';
import quotationRoutes from '@/routes/quotations';
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
    quotation: {
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
            pr: existing.pr ?? '',
            description: existing.description ?? '',
            unit: existing.unit ?? 'Each',
            quantity: existing.quantity ?? '',
            rate_per_unit: existing.rate_per_unit ?? '',
        };
    }

    return {
        key: `new-${Date.now()}-${Math.random()}`,
        dbId: null,
        pr: '',
        description: '',
        unit: 'Each',
        quantity: '',
        rate_per_unit: '',
    };
}

const selectedCompanyId = ref(props.quotation.company_id ?? null);
const quotationDate = ref(formatDateForInput(props.quotation.date));
const validityDays = ref(props.quotation.validity ?? 7);

const items = ref(
    props.quotation.items?.length
        ? props.quotation.items.map((item) => createItem(item))
        : [createItem()]
);

function getLineTotal(item) {
    const qty = parseFloat(item.quantity);
    const rate = parseFloat(item.rate_per_unit);
    if (Number.isNaN(qty) || Number.isNaN(rate)) {
        return 0;
    }
    return +(qty * rate).toFixed(2);
}

const grandTotal = computed(() => {
    return +items.value
        .reduce((sum, item) => sum + getLineTotal(item), 0)
        .toFixed(2);
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
            {
                title: 'Quotations',
                href: '/quotations',
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

    <Head :title="`Edit Quotation ${quotation.quotation_number ?? ''}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Card class="w-full">
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle>
                        Edit Quotation
                        <span v-if="quotation.quotation_number" class="ml-2 text-sm font-normal text-muted-foreground">
                            ({{ quotation.quotation_number }})
                        </span>
                    </CardTitle>
                </div>
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="quotationRoutes.index ? deliveryChallanRoutes.index.url() : '/quotations'">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </CardHeader>

            <CardContent>
                <Form v-bind="QuotationController.update.form({ quotation: quotation.id })"
                    v-slot="{ errors, processing }">
                    <div class="grid gap-4 sm:grid-cols-3">
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
                            <div class="flex flex-col gap-1.5">
                                <Label for="date">Date</Label>
                                <VueDatePicker v-model="quotationDate" uid="date" placeholder="dd/mm/yyyy"
                                    :formats="{ input: 'dd/MM/yyyy' }" :time-config="{ enableTimePicker: false }"
                                    :auto-apply="true" />
                                <input type="hidden" name="date" :value="formatDisplayDate(quotationDate)" />
                                <InputError class="mt-2" :message="errors.date" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="validity">Validity (Days)</Label>
                            <Input id="validity" v-model="validityDays" name="validity" type="number" min="1"
                                placeholder="e.g. 7" />
                            <InputError class="mt-2" :message="errors.validity" />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Items</h2>
                            <p class="text-sm text-muted-foreground">
                                Update, add, or remove items on this quotation.
                            </p>
                        </div>
                        <Button type="button" variant="outline" @click="addItem">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Item
                        </Button>
                    </div>

                    <InputError class="mt-2" :message="errors.items" />

                    <div class="mt-4 space-y-4">
                        <div v-for="(item, index) in items" :key="item.key"
                            class="grid gap-4 rounded-lg border p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,2.2fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_minmax(0,1.2fr)_auto]">
                            <input v-if="item.dbId" type="hidden" :name="`items[${index}][id]`" :value="item.dbId" />

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-pr-${item.key}`">PR</Label>
                                <Input :id="`item-pr-${item.key}`" v-model="item.pr" :name="`items[${index}][pr]`"
                                    placeholder="PR number" />
                                <InputError class="mt-1" :message="errors[`items.${index}.pr`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-description-${item.key}`">Description</Label>
                                <Input :id="`item-description-${item.key}`" v-model="item.description"
                                    :name="`items[${index}][description]`" placeholder="Enter item description" />
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
                                <InputError class="mt-1" :message="errors[`items.${index}.unit`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-quantity-${item.key}`">Quantity</Label>
                                <Input :id="`item-quantity-${item.key}`" v-model="item.quantity"
                                    :name="`items[${index}][quantity]`" type="number" min="0" step="any"
                                    placeholder="0" />
                                <InputError class="mt-1" :message="errors[`items.${index}.quantity`]" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label :for="`item-rate-${item.key}`">Rate / Unit</Label>
                                <Input :id="`item-rate-${item.key}`" v-model="item.rate_per_unit"
                                    :name="`items[${index}][rate_per_unit]`" type="number" min="0" step="any"
                                    placeholder="0.00" />
                                <InputError class="mt-1" :message="errors[`items.${index}.rate_per_unit`]" />
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

                    <div class="mt-6 flex items-center justify-end border-t pt-4">
                        <div class="text-right">
                            <span class="text-sm text-muted-foreground">Total Amount: </span>
                            <span class="text-lg font-semibold">{{ grandTotal.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <Button type="submit" variant="default" :disabled="processing">
                            <Save class="mr-2 h-4 w-4" />
                            {{ processing ? 'Updating...' : 'Update Quotation' }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
