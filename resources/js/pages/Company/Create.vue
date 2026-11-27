<script setup>
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
import { Plus } from '@lucide/vue';
import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import companyRoutes from '@/routes/companies';
import InputError from '@/components/InputError.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Companies', href: '/companies' },
            { title: 'Create', href: '/companies/create' },
        ],
    },
});
</script>

<template>
    <Head title="Create Company" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Card class="w-full">
            <CardHeader>
                <CardTitle>Create Company</CardTitle>
            </CardHeader>

            <CardContent>
                <Form v-bind="CompanyController.store.form()" v-slot="{ errors, processing }">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <Label for="name">Company Name</Label>
                            <Input id="name" name="name" placeholder="Enter company name" />
                            <InputError class="mt-1" :message="errors.name" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="branch">Branch</Label>
                            <Input id="branch" name="branch" placeholder="Enter branch" />
                            <InputError class="mt-1" :message="errors.branch" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="strn">STRN</Label>
                            <Input id="strn" name="strn" placeholder="Enter STRN" />
                            <InputError class="mt-1" :message="errors.strn" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="ntn">NTN</Label>
                            <Input id="ntn" name="ntn" placeholder="Enter NTN" />
                            <InputError class="mt-1" :message="errors.ntn" />
                        </div>

                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <Label for="address">Address</Label>
                            <Input id="address" name="address" placeholder="Enter company address" />
                            <InputError class="mt-1" :message="errors.address" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <Button type="submit" variant="default" :disabled="processing">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ processing ? 'Saving...' : 'Create Company' }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
