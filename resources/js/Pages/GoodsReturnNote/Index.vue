<template>
  <Head title="Goods Return Notes" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 mt-8 space-y-6">
      <!-- Title -->
      <div class="flex items-center justify-between">
        <p class="text-4xl font-bold tracking-wide text-black uppercase">Goods Return Notes</p>
        <Link href="/goods-return-notes/create" class="px-5 py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition">
          + New Return
        </Link>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-red-700 text-white rounded-2xl p-5 shadow">
          <p class="text-sm text-red-200">Total Returns</p>
          <p class="text-3xl font-bold mt-1">{{ totals.total_returns }}</p>
        </div>
        <div class="bg-gray-800 text-white rounded-2xl p-5 shadow">
          <p class="text-sm text-gray-300">Total Returned Value</p>
          <p class="text-3xl font-bold mt-1">Rs. {{ totals.total_value.toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl shadow p-5">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          <input
            v-model="filters.search"
            @input="applyFilters"
            type="text"
            placeholder="Search by number or ref..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
          />
          <select
            v-model="filters.supplier_id"
            @change="applyFilters"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
          >
            <option value="">All Suppliers</option>
            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <input
            v-model="filters.from"
            @change="applyFilters"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
          />
          <input
            v-model="filters.to"
            @change="applyFilters"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
          />
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="px-4 py-3 text-left">GRN Number</th>
              <th class="px-4 py-3 text-left">Supplier</th>
              <th class="px-4 py-3 text-left">Return Date</th>
              <th class="px-4 py-3 text-left">Ref No.</th>
              <th class="px-4 py-3 text-left">Reason</th>
              <th class="px-4 py-3 text-right">Total (Rs.)</th>
              <th class="px-4 py-3 text-center">Items</th>
              <th class="px-4 py-3 text-center">View</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="ret in returns.data"
              :key="ret.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="px-4 py-3 font-mono text-gray-700 font-semibold">{{ ret.grn_number }}</td>
              <td class="px-4 py-3 text-gray-600">{{ ret.supplier?.name ?? '—' }}</td>
              <td class="px-4 py-3 text-gray-600">{{ ret.return_date }}</td>
              <td class="px-4 py-3 text-gray-500">{{ ret.reference_no ?? '—' }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 text-xs rounded-full font-semibold capitalize"
                  :class="reasonClass(ret.reason)">
                  {{ ret.reason?.replace('_', ' ') ?? '—' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800">
                {{ Number(ret.total_amount).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
              </td>
              <td class="px-4 py-3 text-center text-gray-600">{{ ret.items_count }}</td>
              <td class="px-4 py-3 text-center">
                <Link :href="`/goods-return-notes/${ret.id}`" class="px-3 py-1 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700">
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="returns.data.length === 0">
              <td colspan="8" class="px-4 py-10 text-center text-gray-400">No goods return notes found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex gap-2 flex-wrap" v-if="returns.links?.length > 3">
        <component
          v-for="link in returns.links"
          :key="link.label"
          :is="link.url ? Link : 'span'"
          :href="link.url ?? '#'"
          v-html="link.label"
          class="px-3 py-2 rounded-lg text-sm border"
          :class="link.active ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
        />
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
  returns: Object,
  suppliers: Array,
  totals: Object,
  filters: Object,
});

const filters = reactive({
  search:      props.filters?.search      ?? '',
  supplier_id: props.filters?.supplier_id ?? '',
  from:        props.filters?.from        ?? '',
  to:          props.filters?.to          ?? '',
});

const applyFilters = () => {
  router.get('/goods-return-notes', filters, { preserveState: true, replace: true });
};

const reasonClass = (reason) => {
  const map = {
    damaged:    'bg-red-100 text-red-700',
    expired:    'bg-yellow-100 text-yellow-700',
    wrong_item: 'bg-blue-100 text-blue-700',
    overstock:  'bg-purple-100 text-purple-700',
    other:      'bg-gray-100 text-gray-700',
  };
  return map[reason] ?? 'bg-gray-100 text-gray-700';
};
</script>
