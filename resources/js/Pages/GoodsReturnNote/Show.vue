<template>
  <Head title="Goods Return Note Details" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 mt-8 space-y-6">
      <!-- Title -->
      <div class="flex items-center space-x-4">
        <Link href="/goods-return-notes">
          <img src="/images/back-arrow.png" class="w-12 h-12" />
        </Link>
        <p class="text-4xl font-bold tracking-wide text-black uppercase">Goods Return Note</p>
      </div>

      <!-- Header Card -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex flex-wrap justify-between gap-4">
          <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Return Number</p>
            <p class="text-2xl font-bold font-mono text-red-600">{{ returnNote.grn_number }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Supplier</p>
            <p class="text-lg font-semibold text-gray-800">{{ returnNote.supplier?.name ?? 'N/A' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Return Date</p>
            <p class="text-lg font-semibold text-gray-800">{{ returnNote.return_date }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Reason</p>
            <span class="px-3 py-1 text-sm rounded-full font-semibold capitalize"
              :class="reasonClass(returnNote.reason)">
              {{ returnNote.reason?.replace('_', ' ') }}
            </span>
          </div>
          <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Reference No.</p>
            <p class="text-lg font-semibold text-gray-800">{{ returnNote.reference_no ?? '—' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Created By</p>
            <p class="text-lg font-semibold text-gray-800">{{ returnNote.created_by?.name ?? '—' }}</p>
          </div>
        </div>
        <div v-if="returnNote.notes" class="mt-4 bg-gray-50 rounded-xl p-4">
          <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Notes</p>
          <p class="text-gray-700 text-sm">{{ returnNote.notes }}</p>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="bg-red-700 text-white rounded-2xl shadow p-6">
        <p class="text-sm text-red-200">Total Return Value</p>
        <p class="text-3xl font-bold mt-1">
          Rs. {{ Number(returnNote.total_amount).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
        </p>
      </div>

      <!-- Items Table -->
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="text-lg font-bold text-gray-700">Returned Items</h3>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="px-4 py-3 text-left">#</th>
              <th class="px-4 py-3 text-left">Product</th>
              <th class="px-4 py-3 text-center">Qty Returned</th>
              <th class="px-4 py-3 text-right">Unit Cost</th>
              <th class="px-4 py-3 text-right">Total</th>
              <th class="px-4 py-3 text-left">Notes</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, idx) in returnNote.items"
              :key="item.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="px-4 py-3 text-gray-500">{{ idx + 1 }}</td>
              <td class="px-4 py-3">
                <p class="font-semibold text-gray-800">{{ item.product?.name }}</p>
                <p class="text-xs text-gray-400">{{ item.product?.code }}</p>
              </td>
              <td class="px-4 py-3 text-center text-gray-700 font-semibold">{{ item.quantity }}</td>
              <td class="px-4 py-3 text-right text-gray-700">
                Rs. {{ Number(item.unit_cost).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
              </td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800">
                Rs. {{ Number(item.total_cost).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
              </td>
              <td class="px-4 py-3 text-gray-500 text-xs">{{ item.notes ?? '—' }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-red-50">
              <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-700">Grand Total</td>
              <td class="px-4 py-3 text-right font-bold text-red-700 text-lg">
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
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';

defineProps({
  returnNote: Object,
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
