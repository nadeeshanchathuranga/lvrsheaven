<template>
  <Head title="Goods Return Note Details" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 mt-8 space-y-6">
      <!-- Title -->
      <div class="flex items-center space-x-6">
        <Link href="/goods-return-notes">
          <img src="/images/back-arrow.png" class="w-16 h-16" />
        </Link>
        <p class="text-5xl font-bold tracking-wide text-black uppercase">Goods Return Note</p>
      </div>

      <!-- Header Card -->
      <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex flex-wrap justify-between gap-6">
          <div>
            <p class="text-base text-gray-400 uppercase tracking-wide font-bold">Return Number</p>
            <p class="text-3xl font-bold font-mono text-red-600 mt-1">{{ returnNote.grn_number }}</p>
          </div>
          <div>
            <p class="text-base text-gray-400 uppercase tracking-wide font-bold">Supplier</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ returnNote.supplier?.name ?? 'N/A' }}</p>
          </div>
          <div>
            <p class="text-base text-gray-400 uppercase tracking-wide font-bold">Return Date</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ returnNote.return_date }}</p>
          </div>
          <div>
            <p class="text-base text-gray-400 uppercase tracking-wide font-bold">Reason</p>
            <span class="px-4 py-2 text-base rounded-full font-bold capitalize"
              :class="reasonClass(returnNote.reason)">
              {{ returnNote.reason?.replace('_', ' ') }}
            </span>
          </div>
          <div>
            <p class="text-base text-gray-400 uppercase tracking-wide font-bold">Reference No.</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ returnNote.reference_no ?? '—' }}</p>
          </div>
          <div>
            <p class="text-base text-gray-400 uppercase tracking-wide font-bold">Created By</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ returnNote.created_by?.name ?? '—' }}</p>
          </div>
        </div>
        <div v-if="returnNote.notes" class="mt-6 bg-gray-50 rounded-xl p-5">
          <p class="text-base text-gray-400 uppercase tracking-wide mb-2 font-bold">Notes</p>
          <p class="text-gray-700 text-lg">{{ returnNote.notes }}</p>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="bg-red-700 text-white rounded-2xl shadow-lg p-8">
        <p class="text-lg text-red-200 font-bold">Total Return Value</p>
        <p class="text-4xl font-bold mt-2">
          Rs. {{ Number(returnNote.total_amount).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
        </p>
      </div>

      <!-- Items Table -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="px-8 py-5 border-b-2 border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-700">Returned Items</h3>
            <span class="text-lg text-gray-500 font-semibold">{{ filteredItems.length }} of {{ returnNote.items?.length ?? 0 }} items</span>
          </div>
          <!-- Search Input -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by product name, code, or barcode..."
              class="w-full border-2 border-gray-300 rounded-xl px-5 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-red-400"
            />
            <svg v-if="searchQuery" @click="searchQuery = ''" class="absolute right-4 top-3.5 h-6 w-6 text-gray-400 cursor-pointer hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        <table class="w-full text-lg">
          <thead class="bg-red-700 text-white">
            <tr>
              <th class="px-6 py-5 text-left text-xl">#</th>
              <th class="px-6 py-5 text-left text-xl">Product</th>
              <th class="px-6 py-5 text-left text-xl">Barcode</th>
              <th class="px-6 py-5 text-center text-xl">Qty Returned</th>
              <th class="px-6 py-5 text-right text-xl">Unit Cost</th>
              <th class="px-6 py-5 text-right text-xl">Total</th>
              <th class="px-6 py-5 text-left text-xl">Notes</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, idx) in filteredItems"
              :key="item.id"
              class="border-b-2 border-gray-100 hover:bg-gray-50"
            >
              <td class="px-6 py-4 text-gray-500 text-lg">{{ idx + 1 }}</td>
              <td class="px-6 py-4">
                <p class="font-bold text-xl text-gray-800">{{ item.product?.name }}</p>
                <p class="text-base text-gray-400">Code: {{ item.product?.code ?? 'N/A' }}</p>
              </td>
              <td class="px-6 py-4">
                <p class="font-mono text-lg text-gray-700">{{ item.product?.barcode ?? 'N/A' }}</p>
              </td>
              <td class="px-6 py-4 text-center text-gray-700 font-bold text-xl">{{ item.quantity }}</td>
              <td class="px-6 py-4 text-right text-gray-700 text-lg">
                Rs. {{ Number(item.unit_cost).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
              </td>
              <td class="px-6 py-4 text-right font-bold text-xl text-gray-800">
                Rs. {{ Number(item.total_cost).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
              </td>
              <td class="px-6 py-4 text-gray-500 text-base">{{ item.notes ?? '—' }}</td>
            </tr>
            <tr v-if="filteredItems.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-400 text-xl">
                No items match your search "{{ searchQuery }}"
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-red-50">
              <td colspan="5" class="px-6 py-4 text-right font-bold text-gray-700 text-xl">Grand Total</td>
              <td class="px-6 py-4 text-right font-bold text-red-700 text-2xl">
                Rs. {{ Number(returnNote.total_amount).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
              </td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
  returnNote: Object,
});

const searchQuery = ref('');

const filteredItems = computed(() => {
  if (!searchQuery.value) return props.returnNote.items || [];
  const query = searchQuery.value.toLowerCase();
  return (props.returnNote.items || []).filter(item => {
    const name = item.product?.name?.toLowerCase() || '';
    const code = item.product?.code?.toLowerCase() || '';
    const barcode = item.product?.barcode?.toLowerCase() || '';
    return name.includes(query) || code.includes(query) || barcode.includes(query);
  });
});

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
