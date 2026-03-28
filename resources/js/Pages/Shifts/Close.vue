<template>
  <Head title="Close Shift" />
  <Banner />
  <div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center px-4">

    <div class="w-full max-w-lg">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-600 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">Close Shift</h1>
        <p class="text-gray-400 mt-1">Count and enter the cash in the till to close this shift.</p>
      </div>

      <!-- Shift summary card -->
      <div class="bg-white rounded-2xl shadow-2xl p-8 space-y-6">

        <!-- Shift info -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500 uppercase font-semibold">Shift Number</p>
            <p class="text-lg font-bold text-gray-800 mt-1">{{ shift.shift_number }}</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500 uppercase font-semibold">Cashier</p>
            <p class="text-lg font-bold text-gray-800 mt-1">{{ shift.user?.name ?? '—' }}</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500 uppercase font-semibold">Opened At</p>
            <p class="text-sm font-semibold text-gray-800 mt-1">{{ formatDT(shift.start_time) }}</p>
          </div>
          <div class="bg-green-50 rounded-xl p-4 border border-green-200">
            <p class="text-xs text-green-700 uppercase font-semibold">Opening Float</p>
            <p class="text-lg font-bold text-green-700 mt-1">Rs. {{ fmt(shift.opening_float) }}</p>
          </div>
          <div class="bg-blue-50 rounded-xl p-4 border border-blue-200 col-span-2">
            <p class="text-xs text-blue-700 uppercase font-semibold">Total Sales This Shift</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">Rs. {{ fmt(totalSales) }}</p>
          </div>
        </div>

        <!-- Discrepancy preview -->
        <div v-if="form.closing_float !== ''" class="p-4 rounded-xl border"
          :class="discrepancy >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
          <p class="text-sm font-semibold" :class="discrepancy >= 0 ? 'text-green-700' : 'text-red-700'">
            Cash Discrepancy:
            <span class="text-lg font-bold">Rs. {{ fmt(Math.abs(discrepancy)) }}</span>
            <span class="ml-2 text-xs">({{ discrepancy >= 0 ? 'Surplus' : 'Shortage' }})</span>
          </p>
          <p class="text-xs mt-1 text-gray-500">Expected = Opening Float + Total Sales = Rs. {{ fmt(expectedCash) }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Closing Float -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Closing Cash in Till (Rs.) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.closing_float"
              type="number"
              min="0"
              step="0.01"
              placeholder="Enter cash counted in till"
              required
              class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-lg font-semibold focus:outline-none focus:border-red-500 text-gray-800"
            />
            <p v-if="errors.closing_float" class="text-red-500 text-xs mt-1">{{ errors.closing_float }}</p>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Closing Notes (optional)</label>
            <textarea
              v-model="form.notes"
              rows="2"
              placeholder="Any handover notes..."
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-red-500 text-gray-700 resize-none"
            ></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <Link
              href="/pos"
              class="flex-1 text-center border-2 border-gray-300 text-gray-700 font-semibold py-3 rounded-xl hover:bg-gray-100 transition"
            >
              Back to POS
            </Link>
            <button
              type="submit"
              :disabled="submitting"
              class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="submitting">Closing...</span>
              <span v-else>🔴 Close Shift</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
  shift: Object,
  totalSales: Number,
});

const page = usePage();
const errors = computed(() => page.props.errors ?? {});

const form = ref({ closing_float: '', notes: '' });
const submitting = ref(false);

const fmt = (val) => Number(val ?? 0).toLocaleString('en-LK', { minimumFractionDigits: 2 });
const formatDT = (dt) => dt ? new Date(dt).toLocaleString('en-LK') : '—';

const expectedCash = computed(() =>
  (parseFloat(props.shift.opening_float) || 0) + (parseFloat(props.totalSales) || 0)
);

const discrepancy = computed(() =>
  (parseFloat(form.value.closing_float) || 0) - expectedCash.value
);

const submit = () => {
  submitting.value = true;
  router.post(`/shifts/${props.shift.id}/close`, {
    closing_float: form.value.closing_float,
    notes: form.value.notes,
  }, {
    onError: () => { submitting.value = false; },
    onFinish: () => { submitting.value = false; },
  });
};
</script>
