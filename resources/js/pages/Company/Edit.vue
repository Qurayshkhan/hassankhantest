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
import { ArrowLeft, Save } from '@lucide/vue';
import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import companyRoutes from '@/routes/companies';
import InputError from '@/components/InputError.vue';

const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
});

const name = ref(props.company.name ?? '');
const branch = ref(props.company.branch ?? '');
const strn = ref(props.company.strn ?? '');
const ntn = ref(props.company.ntn ?? '');
const address = ref(props.company.address ?? '');

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Companies', href: '/companies' },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${company.name ?? 'Company'}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Card class="w-full">
            <CardHeader class="flex flex-row items-center justify-between gap-2">
                <CardTitle>Edit Company</CardTitle>
                  <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="companyRoutes.index ? companyRoutes.index.url() : '/companies'">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </CardHeader>

            <CardContent>
                <Form
                    v-bind="CompanyController.update.form({ company: company.id })"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <Label for="name">Company Name</Label>
                            <Input id="name" v-model="name" name="name" placeholder="Enter company name" />
                            <InputError class="mt-1" :message="errors.name" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="branch">Branch</Label>
                            <Input id="branch" v-model="branch" name="branch" placeholder="Enter branch" />
                            <InputError class="mt-1" :message="errors.branch" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="strn">STRN</Label>
                            <Input id="strn" v-model="strn" name="strn" placeholder="Enter STRN" />
                            <InputError class="mt-1" :message="errors.strn" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="ntn">NTN</Label>
                            <Input id="ntn" v-model="ntn" name="ntn" placeholder="Enter NTN" />
                            <InputError class="mt-1" :message="errors.ntn" />
                        </div>

                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <Label for="address">Address</Label>
                            <Input id="address" v-model="address" name="address" placeholder="Enter company address" />
                            <InputError class="mt-1" :message="errors.address" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <Button type="submit" variant="default" :disabled="processing">
                            <Save class="mr-2 h-4 w-4" />
                            {{ processing ? 'Updating...' : 'Update Company' }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
