<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import companyRoutes from '@/routes/companies';
import Card from '@/components/ui/card/Card.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Pagination from '@/components/Pagination.vue';
import { Building2, Plus, Search, SquarePen } from '@lucide/vue';
import Delete from './Delete.vue';

const props = defineProps({
    companies: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props?.filters?.search ?? '');

watch(search, (value) => {
    const trimmed = value?.trim() ?? '';
    router.get(
        companyRoutes.index.url(),
        trimmed ? { search: trimmed } : {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
});

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Companies', href: '/companies' },
        ],
    },
});
</script>

<template>
    <Head title="Companies" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Companies</h1>
                <p class="text-sm text-muted-foreground">
                    Manage customer companies, branches, STRN, and NTN details.
                </p>
            </div>

            <Button variant="default" as-child>
                <Link :href="companyRoutes.create.url()">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Create Company
                </Link>
            </Button>
        </div>

        <Card class="overflow-hidden p-0">
            <div class="border-b p-4">
                <div class="relative w-full max-w-xs">
                    <Search class="pointer-events-none absolute top-2.5 left-3 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="text"
                        class="pl-9"
                        placeholder="Search name, branch, NTN..."
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full caption-bottom border-collapse text-left text-sm">
                    <thead class="border-b bg-muted/50 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Branch</th>
                            <th class="px-4 py-3">STRN</th>
                            <th class="px-4 py-3">NTN</th>
                            <th class="px-4 py-3">Address</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr
                            v-for="(company, index) in companies.data"
                            :key="company.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td class="px-4 py-3 font-medium text-muted-foreground">
                                {{ (companies?.from ?? 1) + index }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ company?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ company?.branch || '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ company?.strn || '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ company?.ntn || '—' }}
                            </td>
                            <td class="max-w-xs truncate px-4 py-3 text-muted-foreground">
                                {{ company?.address || '—' }}
                            </td>
                            <td class="flex items-center justify-end gap-1 px-4 py-3 text-right">
                                <Link
                                    :href="companyRoutes?.edit?.url?.({ company: company.id })"
                                    title="Edit Company"
                                >
                                    <SquarePen class="h-4 w-4 text-blue-900" />
                                </Link>
                                <Delete :id="company?.id" />
                            </td>
                        </tr>

                        <tr v-if="!companies.data?.length">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                        <Building2 class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <p class="font-medium">No companies found</p>
                                    <p class="text-xs text-muted-foreground">
                                        Get started by creating your first company.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="companies.data?.length"
                :links="companies.links"
                :from="companies.from"
                :to="companies.to"
                :total="companies.total"
            />
        </Card>
    </div>
</template>
