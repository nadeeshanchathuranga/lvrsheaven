<template>
  <Head :title="`GRN - ${grn.grn_number}`" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 mt-8 space-y-6">
      <!-- Title -->
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
          <Link href="/grn">
            <img src="/images/back-arrow.png" class="w-12 h-12" />
          </Link>
          <div>
            <p class="text-4xl font-bold tracking-wide text-black uppercase">{{ grn.grn_number }}</p>
            <p class="text-sm text-gray-500">Goods Received Note</p>
          </div>
        </div>
        <span :class="statusClass(grn.payment_status)" class="px-4 py-2 rounded-full text-sm font-bold uppercase">
          {{ grn.payment_status }}
        </span>
      </div>

      <!-- GRN Details Card -->
      <div class="bg-white rounded-2xl shadow p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
        <div>
          <p class="text-xs text-gray-400 uppercase font-semibold">Supplier</p>
          <p class="font-bold text-gray-800 mt-1">{{ grn.supplier?.name ?? '—' }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-400 uppercase font-semibold">GRN Date</p>
          <p class="font-bold text-gray-800 mt-1">{{ formatDate(grn.grn_date) }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-400 uppercase font-semibold">Reference No.</p>
          <p class="font-bold text-gray-800 mt-1">{{ grn.reference_no || '—' }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-400 uppercase font-semibold">Created By</p>
          <p class="font-bold text-gray-800 mt-1">{{ grn.created_by?.name ?? '—' }}</p>
        </div>
        <div class="col-span-2 md:col-span-4" v-if="grn.notes">
          <p class="text-xs text-gray-400 uppercase font-semibold">Notes</p>
          <p class="text-gray-700 mt-1">{{ grn.notes }}</p>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-blue-500">
          <p class="text-xs text-gray-400 uppercase font-semibold">Total Amount</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">{{ formatCurrency(grn.total_amount) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-green-500">
          <p class="text-xs text-gray-400 uppercase font-semibold">Total Paid</p>
          <p class="text-2xl font-bold text-green-700 mt-1">{{ formatCurrency(grn.paid_amount) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-red-500">
          <p class="text-xs text-gray-400 uppercase font-semibold">Outstanding</p>
          <p class="text-2xl font-bold mt-1" :class="outstanding > 0 ? 'text-red-600' : 'text-green-600'">
            {{ formatCurrency(outstanding) }}
          </p>
        </div>
      </div>

      <!-- Items Table -->
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Received Items</h3>
            <span class="text-sm text-gray-500">{{ filteredItems.length }} of {{ grn.items?.length ?? 0 }} items</span>
          </div>
          <!-- Search Input -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by product name, code, or barcode..."
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
            <svg v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 cursor-pointer hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-gray-700">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200 text-[13px] text-gray-500 uppercase">
                <th class="px-6 py-3 text-left">#</th>
                <th class="px-6 py-3 text-left">Product</th>
                <th class="px-6 py-3 text-left">Category</th>
                <th class="px-6 py-3 text-left">Barcode</th>
                <th class="px-6 py-3 text-center">Qty Received</th>
                <th class="px-6 py-3 text-center">Unit Cost</th>
                <th class="px-6 py-3 text-center">Selling Price</th>
                <th class="px-6 py-3 text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, idx) in filteredItems"
                :key="item.id"
                class="border-b border-gray-100 hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4 text-gray-400">{{ idx + 1 }}</td>
                <td class="px-6 py-4">
                  <p class="font-semibold text-gray-800">{{ item.product?.name ?? '—' }}</p>
                  <p class="text-xs text-gray-400">Code: {{ item.product?.code ?? 'N/A' }}</p>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded">
                    {{ item.product?.category?.name ?? 'Uncategorized' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <p class="font-mono text-sm text-gray-700">{{ item.product?.barcode ?? 'N/A' }}</p>
                </td>
                <td class="px-6 py-4 text-center font-bold text-blue-700">{{ item.quantity }}</td>
                <td class="px-6 py-4 text-center">{{ formatCurrency(item.unit_cost) }}</td>
                <td class="px-6 py-4 text-center font-semibold text-green-700">{{ formatCurrency(item.product?.selling_price ?? 0) }}</td>
                <td class="px-6 py-4 text-right font-semibold">{{ formatCurrency(item.total_cost) }}</td>
              </tr>
              <tr v-if="filteredItems.length === 0">
                <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                  No items match your search "{{ searchQuery }}"
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="bg-gray-50 border-t-2 border-gray-300">
                <td colspan="7" class="px-6 py-4 text-right font-bold text-gray-700">Grand Total</td>
                <td class="px-6 py-4 text-right font-bold text-xl text-gray-900">{{ formatCurrency(grn.total_amount) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Payments Section -->
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-lg font-bold text-gray-800">Payment History</h3>
          <button
            v-if="HasRole(['Admin', 'Manager']) && outstanding > 0"
            @click="showPaymentForm = !showPaymentForm"
            class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition"
          >
            <i class="ri-add-circle-line mr-1"></i> Record Payment
          </button>
        </div>

        <!-- Add Payment Form -->
        <div v-if="showPaymentForm" class="px-6 py-4 border-b border-gray-100 bg-green-50">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Amount (Rs.) <span class="text-red-500">*</span></label>
              <input
                v-model.number="paymentForm.amount"
                type="number"
                min="0.01"
                step="0.01"
                :max="outstanding"
                :placeholder="`Max: ${formatCurrency(outstanding)}`"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Date <span class="text-red-500">*</span></label>
              <input
                v-model="paymentForm.payment_date"
                type="date"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Method</label>
              <select
                v-model="paymentForm.payment_method"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
              >
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cheque">Cheque</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
              <input
                v-model="paymentForm.notes"
                type="text"
                placeholder="Optional"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
              />
            </div>
          </div>
          <p v-if="paymentError" class="text-red-500 text-sm mt-2">{{ paymentError }}</p>
          <div class="flex gap-3 mt-3">
            <button
              @click="savePayment"
              :disabled="savingPayment"
              class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition disabled:opacity-50"
            >
              {{ savingPayment ? 'Saving...' : 'Save Payment' }}
            </button>
            <button
              @click="showPaymentForm = false"
              class="px-4 py-2 bg-gray-200 text-gray-600 rounded-lg text-sm"
            >
              Cancel
            </button>
          </div>
        </div>

        <!-- Payment Records -->
        <div class="overflow-x-auto">
          <table v-if="grn.payments?.length > 0" class="w-full text-sm text-gray-700">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200 text-[13px] text-gray-500 uppercase">
                <th class="px-6 py-3 text-left">Date</th>
                <th class="px-6 py-3 text-right">Amount</th>
                <th class="px-6 py-3 text-center">Method</th>
                <th class="px-6 py-3 text-left">Notes</th>
                <th class="px-6 py-3 text-left">Recorded By</th>
                <th class="px-6 py-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="payment in grn.payments"
                :key="payment.id"
                class="border-b border-gray-100 hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4">{{ formatDate(payment.payment_date) }}</td>
                <td class="px-6 py-4 text-right font-semibold text-green-700">{{ formatCurrency(payment.amount) }}</td>
                <td class="px-6 py-4 text-center">
                  <span class="px-2 py-1 bg-gray-100 rounded text-xs capitalize">{{ payment.payment_method.replace('_', ' ') }}</span>
                </td>
                <td class="px-6 py-4 text-gray-500">{{ payment.notes || '—' }}</td>
                <td class="px-6 py-4 text-gray-500">{{ payment.created_by?.name ?? '—' }}</td>
                <td class="px-6 py-4 text-center">
                  <button
                    v-if="HasRole(['Admin'])"
                    @click="deletePayment(payment.id)"
                    class="text-red-400 hover:text-red-600 text-xs"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="px-6 py-8 text-center text-gray-400">No payments recorded yet</div>
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';
import { HasRole } from '@/Utils/Permissions';

const props = defineProps({
  grn: Object,
});

const searchQuery = ref('');

const filteredItems = computed(() => {
  if (!searchQuery.value) return props.grn.items || [];
  const query = searchQuery.value.toLowerCase();
  return (props.grn.items || []).filter(item => {
    const name = item.product?.name?.toLowerCase() || '';
    const code = item.product?.code?.toLowerCase() || '';
    const barcode = item.product?.barcode?.toLowerCase() || '';
    return name.includes(query) || code.includes(query) || barcode.includes(query);
  });
});

const outstanding = computed(() => parseFloat(props.grn.total_amount) - parseFloat(props.grn.paid_amount));

const showPaymentForm = ref(false);
const savingPayment = ref(false);
const paymentError = ref('');

const today = new Date().toISOString().slice(0, 10);

const paymentForm = reactive({
  amount: '',
  payment_date: today,
  payment_method: 'cash',
  notes: '',
});

const savePayment = () => {
  paymentError.value = '';
  if (!paymentForm.amount || paymentForm.amount <= 0) {
    paymentError.value = 'Please enter a valid amount.';
    return;
  }
  if (!paymentForm.payment_date) {
    paymentError.value = 'Please select a payment date.';
    return;
  }
  savingPayment.value = true;
  router.post('/supplier-payments', {
    supplier_id: props.grn.supplier_id,
    grn_id: props.grn.id,
    amount: paymentForm.amount,
    payment_date: paymentForm.payment_date,
    payment_method: paymentForm.payment_method,
    notes: paymentForm.notes,
  }, {
    onSuccess: () => {
      showPaymentForm.value = false;
      paymentForm.amount = '';
      paymentForm.notes = '';
      savingPayment.value = false;
    },
    onError: (err) => {
      paymentError.value = Object.values(err)[0] ?? 'An error occurred.';
      savingPayment.value = false;
    },
  });
};

const deletePayment = (id) => {
  if (!confirm('Delete this payment record?')) return;
  router.delete(`/supplier-payments/${id}`);
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
