<template>
  <Head title="GRN - Goods Received Notes" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 space-y-6">
      <!-- Page Title -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-12 h-12" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Goods Received Notes
          </p>
        </div>
        <Link
          v-if="HasRole(['Admin', 'Manager'])"
          href="/grn/create"
          class="px-8 py-3 text-xl font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition"
        >
          <i class="ri-add-circle-fill pr-2"></i> New GRN
        </Link>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-blue-500">
          <p class="text-sm text-gray-500">Total GRNs</p>
          <p class="text-2xl font-bold text-gray-800">{{ totals.total_grns }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-purple-500">
          <p class="text-sm text-gray-500">Total Value</p>
          <p class="text-2xl font-bold text-gray-800">{{ formatCurrency(totals.total_value) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
          <p class="text-sm text-gray-500">Total Paid</p>
          <p class="text-2xl font-bold text-green-700">{{ formatCurrency(totals.total_paid) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-red-500">
          <p class="text-sm text-gray-500">Outstanding</p>
          <p class="text-2xl font-bold text-red-600">{{ formatCurrency(totals.total_outstanding) }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow p-4 flex flex-wrap gap-3">
        <input
          v-model="filters.search"
          @input="search"
          type="text"
          placeholder="Search GRN# or Reference..."
          class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 w-56"
        />
        <select
          v-model="filters.supplier_id"
          @change="search"
          class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
        >
          <option value="">All Suppliers</option>
          <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
        <select
          v-model="filters.status"
          @change="search"
          class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
        >
          <option value="">All Statuses</option>
          <option value="unpaid">Unpaid</option>
          <option value="partial">Partial</option>
          <option value="paid">Paid</option>
        </select>
        <input
          v-model="filters.from"
          @change="search"
          type="date"
          class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
        <input
          v-model="filters.to"
          @change="search"
          type="date"
          class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
        <button
          @click="resetFilters"
          class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200"
        >
          Reset
        </button>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
          <thead>
            <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-white text-[14px]">
              <th class="p-4 text-left font-semibold uppercase">GRN #</th>
              <th class="p-4 text-left font-semibold uppercase">Supplier</th>
              <th class="p-4 text-left font-semibold uppercase">Date</th>
              <th class="p-4 text-left font-semibold uppercase">Ref No.</th>
              <th class="p-4 text-right font-semibold uppercase">Total</th>
              <th class="p-4 text-right font-semibold uppercase">Paid</th>
              <th class="p-4 text-right font-semibold uppercase">Outstanding</th>
              <th class="p-4 text-center font-semibold uppercase">Status</th>
              <th class="p-4 text-center font-semibold uppercase">Items</th>
              <th class="p-4 text-center font-semibold uppercase">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="grn in grns.data"
              :key="grn.id"
              class="hover:bg-gray-50 border-b border-gray-100 transition"
            >
              <td class="p-4 font-bold text-blue-700">{{ grn.grn_number }}</td>
              <td class="p-4">{{ grn.supplier?.name ?? '—' }}</td>
              <td class="p-4">{{ formatDate(grn.grn_date) }}</td>
              <td class="p-4 text-gray-500">{{ grn.reference_no || '—' }}</td>
              <td class="p-4 text-right font-semibold">{{ formatCurrency(grn.total_amount) }}</td>
              <td class="p-4 text-right text-green-700">{{ formatCurrency(grn.paid_amount) }}</td>
              <td class="p-4 text-right" :class="(grn.total_amount - grn.paid_amount) > 0 ? 'text-red-600 font-semibold' : 'text-green-600'">
                {{ formatCurrency(grn.total_amount - grn.paid_amount) }}
              </td>
              <td class="p-4 text-center">
                <span :class="statusClass(grn.payment_status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase">
                  {{ grn.payment_status }}
                </span>
              </td>
              <td class="p-4 text-center text-gray-600">{{ grn.items_count }}</td>
              <td class="p-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <Link :href="`/grn/${grn.id}`" class="px-3 py-1 bg-blue-600 text-white rounded-lg text-xs hover:bg-blue-700 transition">
                    View
                  </Link>
                  <a
                    :href="`/grn/${grn.id}?download=1`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="px-3 py-1 bg-orange-600 text-white rounded-lg text-xs hover:bg-orange-700 transition"
                  >
                    PDF
                  </a>
                </div>
              </td>
            </tr>
            <tr v-if="grns.data.length === 0">
              <td colspan="10" class="p-8 text-center text-gray-400">No GRNs found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between text-sm text-gray-500">
        <span>Showing {{ grns.from ?? 0 }}–{{ grns.to ?? 0 }} of {{ grns.total }} records</span>
        <div class="flex gap-2">
          <Link
            v-for="link in grns.links"
            :key="link.label"
            :href="link.url || '#'"
            :class="[
              'px-3 py-1 rounded border',
              link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50',
              !link.url ? 'opacity-40 pointer-events-none' : ''
            ]"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';
import { HasRole } from '@/Utils/Permissions';

const props = defineProps({
  grns: Object,
  suppliers: Array,
  totals: Object,
  filters: Object,
});

const filters = reactive({
  search: props.filters?.search ?? '',
  supplier_id: props.filters?.supplier_id ?? '',
  status: props.filters?.status ?? '',
  from: props.filters?.from ?? '',
  to: props.filters?.to ?? '',
});

let searchTimer = null;
const search = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    router.get('/grn', filters, { preserveState: true, replace: true });
  }, 400);
};

const resetFilters = () => {
  filters.search = '';
  filters.supplier_id = '';
  filters.status = '';
  filters.from = '';
  filters.to = '';
  search();
};

const formatCurrency = (val) =>
  'Rs. ' + parseFloat(val ?? 0).toLocaleString('en-LK', { minimumFractionDigits: 2 });

const formatDate = (d) => {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-GB');
};

const statusClass = (status) => {
  if (status === 'paid') return 'bg-green-100 text-green-700';
  if (status === 'partial') return 'bg-yellow-100 text-yellow-700';
  return 'bg-red-100 text-red-700';
};
</script>
