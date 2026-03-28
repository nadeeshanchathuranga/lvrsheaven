<template>
  <Head title="Create Goods Return Note" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 mt-8 space-y-6">
      <!-- Title -->
      <div class="flex items-center space-x-4">
        <Link href="/goods-return-notes">
          <img src="/images/back-arrow.png" class="w-12 h-12" />
        </Link>
        <p class="text-4xl font-bold tracking-wide text-black uppercase">New Goods Return Note</p>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl shadow p-6 space-y-6">
        <!-- Header Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Supplier -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Supplier</label>
            <select
              v-model="form.supplier_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
            >
              <option value="">Select supplier (optional)...</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <!-- Return Date -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Return Date <span class="text-red-500">*</span></label>
            <input
              v-model="form.return_date"
              type="date"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
            />
            <p v-if="errors.return_date" class="text-red-500 text-xs mt-1">{{ errors.return_date }}</p>
          </div>

          <!-- Reason -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Return Reason <span class="text-red-500">*</span></label>
            <select
              v-model="form.reason"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
            >
              <option value="damaged">Damaged</option>
              <option value="expired">Expired</option>
              <option value="wrong_item">Wrong Item</option>
              <option value="overstock">Overstock</option>
              <option value="other">Other</option>
            </select>
          </div>

          <!-- Reference No -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Reference No.</label>
            <input
              v-model="form.reference_no"
              type="text"
              placeholder="Optional reference"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
            />
          </div>

          <!-- Notes -->
          <div class="md:col-span-2 lg:col-span-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
            <input
              v-model="form.notes"
              type="text"
              placeholder="Optional additional notes..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
            />
          </div>
        </div>

        <!-- Product Search & Add -->
        <div class="border-t pt-4">
          <h3 class="text-lg font-bold text-gray-700 mb-3">Add Products to Return <span class="text-red-500">*</span></h3>
          <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-64 relative">
              <label class="block text-sm font-semibold text-gray-700 mb-1">Search Product</label>
              <input
                v-model="productSearch"
                @input="searchProducts"
                type="text"
                placeholder="Search by name or code..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
              />
              <!-- Dropdown -->
              <div
                v-if="searchResults.length > 0"
                class="absolute z-50 top-full mt-1 left-0 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto"
              >
                <div
                  v-for="p in searchResults"
                  :key="p.id"
                  @click="selectProduct(p)"
                  class="px-4 py-3 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-0"
                >
                  <p class="font-semibold text-sm text-gray-800">{{ p.name }}</p>
                  <p class="text-xs text-gray-400">Code: {{ p.code }} | Stock: {{ p.stock_quantity }} | Cost: Rs. {{ p.cost_price }}</p>
                </div>
              </div>
              <p v-if="searching" class="text-xs text-gray-400 mt-1">Searching...</p>
            </div>
          </div>

          <!-- Items Table -->
          <div class="mt-4 overflow-x-auto" v-if="form.items.length > 0">
            <table class="w-full text-sm border-collapse">
              <thead>
                <tr class="bg-red-700 text-white">
                  <th class="px-4 py-3 text-left">#</th>
                  <th class="px-4 py-3 text-left">Product</th>
                  <th class="px-4 py-3 text-left">Current Stock</th>
                  <th class="px-4 py-3 text-center">Qty Returned <span class="text-red-200">*</span></th>
                  <th class="px-4 py-3 text-center">Unit Cost (Rs.) <span class="text-red-200">*</span></th>
                  <th class="px-4 py-3 text-center">Notes</th>
                  <th class="px-4 py-3 text-right">Total</th>
                  <th class="px-4 py-3 text-center">Remove</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, idx) in form.items"
                  :key="item.product_id"
                  class="border-b border-gray-100 hover:bg-gray-50"
                >
                  <td class="px-4 py-3 text-gray-500">{{ idx + 1 }}</td>
                  <td class="px-4 py-3">
                    <p class="font-semibold text-gray-800">{{ item.product_name }}</p>
                    <p class="text-xs text-gray-400">{{ item.product_code }}</p>
                  </td>
                  <td class="px-4 py-3 text-gray-500">{{ item.current_stock }}</td>
                  <td class="px-4 py-3">
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      :max="item.current_stock"
                      class="w-24 border border-gray-300 rounded px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-red-400"
                    />
                    <p v-if="itemErrors[idx]?.quantity" class="text-red-500 text-xs mt-1">{{ itemErrors[idx].quantity }}</p>
                  </td>
                  <td class="px-4 py-3">
                    <input
                      v-model.number="item.unit_cost"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-28 border border-gray-300 rounded px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-red-400"
                    />
                    <p v-if="itemErrors[idx]?.unit_cost" class="text-red-500 text-xs mt-1">{{ itemErrors[idx].unit_cost }}</p>
                  </td>
                  <td class="px-4 py-3">
                    <input
                      v-model="item.notes"
                      type="text"
                      placeholder="Optional"
                      class="w-28 border border-gray-300 rounded px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-red-400"
                    />
                  </td>
                  <td class="px-4 py-3 text-right font-semibold text-gray-800">
                    Rs. {{ lineTotal(item).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <button @click="removeItem(idx)" class="text-red-500 hover:text-red-700 transition">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="mt-4 py-8 text-center text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
            Search and add products to return using the search box above
          </div>
        </div>

        <!-- Grand Total -->
        <div class="flex justify-end pt-4 border-t">
          <div class="bg-red-700 text-white rounded-xl px-8 py-4 text-right space-y-1">
            <p class="text-sm text-red-200">Total Items: <span class="font-bold text-white">{{ form.items.length }}</span></p>
            <p class="text-2xl font-bold">Total Return: Rs. {{ grandTotal.toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}</p>
          </div>
        </div>

        <!-- Validation Error -->
        <div v-if="globalError" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm">
          {{ globalError }}
        </div>
        <p v-if="errors.items" class="text-red-500 text-sm">{{ errors.items }}</p>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 pt-2">
          <Link href="/goods-return-notes" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition">
            Cancel
          </Link>
          <button
            @click="submit"
            :disabled="submitting"
            class="px-10 py-3 bg-red-600 text-white rounded-xl font-bold text-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="submitting">Saving...</span>
            <span v-else>Save Return Note</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
  suppliers: Array,
});

const page = usePage();
const errors = computed(() => page.props.errors ?? {});

const today = new Date().toISOString().slice(0, 10);

const form = reactive({
  supplier_id:  '',
  return_date:  today,
  reference_no: '',
  reason:       'other',
  notes:        '',
  items:        [],
});

const productSearch = ref('');
const searchResults = ref([]);
const searching     = ref(false);
const submitting    = ref(false);
const globalError   = ref('');
const itemErrors    = reactive({});

let searchTimer = null;
const searchProducts = () => {
  clearTimeout(searchTimer);
  if (!productSearch.value.trim()) {
    searchResults.value = [];
    return;
  }
  searching.value = true;
  searchTimer = setTimeout(async () => {
    try {
      const res = await fetch('/api/products', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ search: productSearch.value }),
      });
      const data = await res.json();
      searchResults.value = data.products?.data ?? [];
    } catch {
      searchResults.value = [];
    } finally {
      searching.value = false;
    }
  }, 350);
};

const selectProduct = (product) => {
  const existing = form.items.find((i) => i.product_id === product.id);
  if (existing) {
    existing.quantity = Math.min(existing.quantity + 1, product.stock_quantity);
  } else {
    form.items.push({
      product_id:    product.id,
      product_name:  product.name,
      product_code:  product.code,
      current_stock: product.stock_quantity,
      quantity:      1,
      unit_cost:     parseFloat(product.cost_price) || 0,
      notes:         '',
    });
  }
  productSearch.value = '';
  searchResults.value = [];
};

const removeItem = (idx) => {
  form.items.splice(idx, 1);
};

const lineTotal = (item) => {
  return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0);
};

const grandTotal = computed(() => {
  return form.items.reduce((sum, i) => sum + lineTotal(i), 0);
});

const validate = () => {
  globalError.value = '';
  let valid = true;

  if (!form.return_date) {
    globalError.value = 'Please select a return date.';
    return false;
  }
  if (form.items.length === 0) {
    globalError.value = 'Please add at least one product to return.';
    return false;
  }

  form.items.forEach((item, idx) => {
    itemErrors[idx] = {};
    if (!item.quantity || item.quantity < 1) {
      itemErrors[idx].quantity = 'Min 1';
      valid = false;
    }
    if (item.quantity > item.current_stock) {
      itemErrors[idx].quantity = `Max ${item.current_stock}`;
      valid = false;
    }
    if (item.unit_cost === null || item.unit_cost === undefined || item.unit_cost < 0) {
      itemErrors[idx].unit_cost = 'Required';
      valid = false;
    }
  });

  return valid;
};

const submit = () => {
  if (!validate()) return;
  submitting.value = true;

  router.post('/goods-return-notes', {
    supplier_id:  form.supplier_id || null,
    return_date:  form.return_date,
    reference_no: form.reference_no,
    reason:       form.reason,
    notes:        form.notes,
    items:        form.items.map((i) => ({
      product_id: i.product_id,
      quantity:   i.quantity,
      unit_cost:  i.unit_cost,
      notes:      i.notes || null,
    })),
  }, {
    onFinish: () => { submitting.value = false; },
    onError:  () => { submitting.value = false; },
  });
};
</script>
