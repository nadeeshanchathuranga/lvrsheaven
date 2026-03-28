<template>
  <Head title="Shifts" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 mt-8 space-y-6">
      <!-- Title -->
      <div class="flex items-center justify-between">
        <p class="text-4xl font-bold tracking-wide text-black uppercase">Shift Management</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl shadow p-4 flex flex-wrap gap-4 items-end">
        <div>
          <label class="block text-sm font-semibold text-gray-600 mb-1">Search Shift #</label>
          <input
            v-model="filterSearch"
            @input="applyFilters"
            type="text"
            placeholder="SHF-2026-..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 w-52"
          />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-600 mb-1">Status</label>
          <select
            v-model="filterStatus"
            @change="applyFilters"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
          >
            <option value="">All</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="px-4 py-3 text-left">Shift #</th>
              <th class="px-4 py-3 text-left">Cashier</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Opened At</th>
              <th class="px-4 py-3 text-left">Closed At</th>
              <th class="px-4 py-3 text-right">Opening Float</th>
              <th class="px-4 py-3 text-right">Closing Float</th>
              <th class="px-4 py-3 text-right">Total Sales</th>
              <th class="px-4 py-3 text-right">Discrepancy</th>
              <th class="px-4 py-3 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="shift in shifts.data"
              :key="shift.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="px-4 py-3 font-semibold text-gray-800">{{ shift.shift_number }}</td>
              <td class="px-4 py-3 text-gray-600">{{ shift.user?.name ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="px-2 py-1 rounded-full text-xs font-bold"
                  :class="shift.status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
                >
                  {{ shift.status === 'open' ? '🟢 Open' : '⚫ Closed' }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-500">{{ formatDT(shift.start_time) }}</td>
              <td class="px-4 py-3 text-gray-500">{{ shift.end_time ? formatDT(shift.end_time) : '—' }}</td>
              <td class="px-4 py-3 text-right text-gray-700">Rs. {{ fmt(shift.opening_float) }}</td>
              <td class="px-4 py-3 text-right text-gray-700">{{ shift.closing_float !== null ? 'Rs. ' + fmt(shift.closing_float) : '—' }}</td>
              <td class="px-4 py-3 text-right font-semibold text-blue-700">Rs. {{ fmt(shift.total_sales) }}</td>
              <td class="px-4 py-3 text-right">
                <template v-if="shift.status === 'closed' && shift.closing_float !== null">
                  <span
                    class="font-semibold"
                    :class="discrepancy(shift) >= 0 ? 'text-green-600' : 'text-red-600'"
                  >
                    {{ discrepancy(shift) >= 0 ? '+' : '' }}Rs. {{ fmt(Math.abs(discrepancy(shift))) }}
                  </span>
                </template>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-4 py-3 text-center">
                <Link
                  v-if="shift.status === 'open'"
                  :href="`/shifts/${shift.id}/close`"
                  class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition"
                >
                  Close
                </Link>
                <span v-else class="text-gray-400 text-xs">Closed</span>
              </td>
            </tr>
            <tr v-if="shifts.data.length === 0">
              <td colspan="10" class="px-4 py-12 text-center text-gray-400">No shifts found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="shifts.last_page > 1" class="flex justify-center gap-2 pt-2">
        <Link
          v-for="link in shifts.links"
          :key="link.label"
          :href="link.url ?? '#'"
          v-html="link.label"
          class="px-3 py-1.5 rounded border text-sm"
          :class="link.active ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
        />
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
  shifts: Object,
  filters: Object,
});

const filterSearch = ref(props.filters?.search ?? '');
const filterStatus = ref(props.filters?.status ?? '');

let filterTimer = null;
const applyFilters = () => {
  clearTimeout(filterTimer);
  filterTimer = setTimeout(() => {
    router.get('/shifts', { search: filterSearch.value, status: filterStatus.value }, { preserveState: true, replace: true });
  }, 300);
};

const fmt = (val) => Number(val ?? 0).toLocaleString('en-LK', { minimumFractionDigits: 2 });
const formatDT = (dt) => dt ? new Date(dt).toLocaleString('en-LK', { dateStyle: 'medium', timeStyle: 'short' }) : '—';

const discrepancy = (shift) => {
  const expected = (parseFloat(shift.opening_float) || 0) + (parseFloat(shift.total_sales) || 0);
  return (parseFloat(shift.closing_float) || 0) - expected;
};
</script>
