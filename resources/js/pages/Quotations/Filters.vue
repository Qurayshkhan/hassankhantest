<script setup>
import Input from '@/components/ui/input/Input.vue';
import { Search } from '@lucide/vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { quotations } from '@/routes';
import { VueDatePicker } from '@vuepic/vue-datepicker';

const props = defineProps({
    filters: Object,
});

const search = ref(props?.filters?.search);
const date = ref(props?.filters?.date);

watch(
    [search, date],
    ([searchValue, dateValue]) => {
        router.get(
            quotations(),
            {
                search: searchValue,
                date: dateValue,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }
);
</script>

<template>
    <div id="filter-block" class="flex flex-col sm:flex-row gap-4 p-4">
        <div>
            <label for="table-search" class="sr-only">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <Search />
                </div>
                <Input
                    v-model="search"
                    type="text"
                    class="block pl-10 text-sm text-gray-900 w-50 bg-gray-50"
                    placeholder="Search.."
                />
            </div>
        </div>
        <VueDatePicker
            v-model="date"
            placeholder="Quotation Date"
            model-type="yyyy-MM-dd"
            format="yyyy-MM-dd"
            :time-config="{ enableTimePicker: false }"
            :auto-apply="true"
        />
    </div>
</template>
