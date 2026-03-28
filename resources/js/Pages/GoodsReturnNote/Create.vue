<template>
  <Head title="Create Goods Return Note" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 mt-8 space-y-6">
      <!-- Title -->
      <div class="flex items-center space-x-6">
        <Link href="/goods-return-notes">
          <img src="/images/back-arrow.png" class="w-16 h-16" />
        </Link>
        <p class="text-5xl font-bold tracking-wide text-black uppercase">New Goods Return Note</p>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl shadow-lg p-8 space-y-8">
        <!-- Header Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Supplier -->
          <div>
            <label class="block text-lg font-bold text-gray-700 mb-3">Supplier</label>
            <select
              v-model="form.supplier_id"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-red-400 text-lg"
            >
              <option value="">Select supplier (optional)...</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <!-- Return Date -->
          <div>
            <label class="block text-lg font-bold text-gray-700 mb-3">Return Date <span class="text-red-500">*</span></label>
            <input
              v-model="form.return_date"
              type="date"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-red-400 text-lg"
            />
            <p v-if="errors.return_date" class="text-red-500 text-base mt-2">{{ errors.return_date }}</p>
          </div>

          <!-- Reason -->
          <div>
            <label class="block text-lg font-bold text-gray-700 mb-3">Return Reason <span class="text-red-500">*</span></label>
            <select
              v-model="form.reason"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-red-400 text-lg"
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
            <label class="block text-lg font-bold text-gray-700 mb-3">Reference No.</label>
            <input
              v-model="form.reference_no"
              type="text"
              placeholder="Optional reference"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-red-400 text-lg"
            />
          </div>

          <!-- Notes -->
          <div class="md:col-span-2 lg:col-span-4">
            <label class="block text-lg font-bold text-gray-700 mb-3">Notes</label>
            <input
              v-model="form.notes"
              type="text"
              placeholder="Optional additional notes..."
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-red-400 text-lg"
            />
          </div>
        </div>

        <!-- Product Search & Add -->
        <div class="border-t-2 pt-6">
          <h3 class="text-2xl font-bold text-gray-700 mb-5">Add Products to Return <span class="text-red-500">*</span></h3>
          <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64 relative">
              <label class="block text-xl font-bold text-gray-700 mb-3">Search Product (Barcode/Name/Code)</label>
              <input
                v-model="productSearch"
                @input="searchProducts"
                type="text"
                placeholder="Scan barcode or search by name/code..."
                class="w-full border-3 border-gray-400 rounded-xl px-6 py-5 focus:outline-none focus:ring-3 focus:ring-red-400 text-xl font-medium"
              />
              <!-- Dropdown -->
              <div
                v-if="searchResults.length > 0"
                class="absolute z-50 top-full mt-2 left-0 w-full bg-white border-2 border-gray-300 rounded-xl shadow-xl max-h-80 overflow-y-auto"
              >
                <div
                  v-for="p in searchResults"
                  :key="p.id"
                  @click="selectProduct(p)"
                  class="px-6 py-4 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-0"
                >
                  <p class="font-bold text-lg text-gray-800">{{ p.name }}</p>
                  <p class="text-base text-gray-400 mt-1">Code: {{ p.code }} | Barcode: {{ p.barcode || 'N/A' }} | Stock: {{ p.stock_quantity }} | Cost: Rs. {{ p.cost_price }}</p>
                </div>
              </div>
              <p v-if="searching" class="text-base text-gray-400 mt-2">Searching...</p>
            </div>
          </div>

          <!-- Items Table -->
          <div class="mt-8 overflow-x-auto" v-if="form.items.length > 0">
            <table class="w-full text-lg border-collapse">
              <thead>
                <tr class="bg-red-700 text-white">
                  <th class="px-6 py-5 text-left text-xl">#</th>
                  <th class="px-6 py-5 text-left text-xl">Product</th>
                  <th class="px-6 py-5 text-left text-xl">Barcode</th>
                  <th class="px-6 py-5 text-left text-xl">Current Stock</th>
                  <th class="px-6 py-5 text-center text-xl">Qty Returned <span class="text-red-200">*</span></th>
                  <th class="px-6 py-5 text-center text-xl">Unit Cost (Rs.) <span class="text-red-200">*</span></th>
                  <th class="px-6 py-5 text-center text-xl">Notes</th>
                  <th class="px-6 py-5 text-right text-xl">Total</th>
                  <th class="px-6 py-5 text-center text-xl">Remove</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, idx) in form.items"
                  :key="item.product_id"
                  class="border-b-2 border-gray-100 hover:bg-gray-50"
                >
                  <td class="px-6 py-4 text-gray-500 text-lg">{{ idx + 1 }}</td>
                  <td class="px-6 py-4">
                    <p class="font-bold text-xl text-gray-800">{{ item.product_name }}</p>
                    <p class="text-base text-gray-400">{{ item.product_code }}</p>
                  </td>
                  <td class="px-6 py-4 text-gray-600 text-lg font-mono">{{ item.product_barcode || 'N/A' }}</td>
                  <td class="px-6 py-4 text-gray-500 text-lg font-semibold">{{ item.current_stock }}</td>
                  <td class="px-6 py-4">
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      :max="item.current_stock"
                      class="w-32 border-2 border-gray-300 rounded-xl px-4 py-3 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-red-400"
                    />
                    <p v-if="itemErrors[idx]?.quantity" class="text-red-500 text-base mt-2">{{ itemErrors[idx].quantity }}</p>
                  </td>
                  <td class="px-6 py-4">
                    <input
                      v-model.number="item.unit_cost"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-40 border-2 border-gray-300 rounded-xl px-4 py-3 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-red-400"
                    />
                    <p v-if="itemErrors[idx]?.unit_cost" class="text-red-500 text-base mt-2">{{ itemErrors[idx].unit_cost }}</p>
                  </td>
                  <td class="px-6 py-4">
                    <input
                      v-model="item.notes"
                      type="text"
                      placeholder="Optional"
                      class="w-36 border-2 border-gray-300 rounded-xl px-4 py-3 text-center text-base focus:outline-none focus:ring-2 focus:ring-red-400"
                    />
                  </td>
                  <td class="px-6 py-4 text-right font-bold text-xl text-gray-800">
                    Rs. {{ lineTotal(item).toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}
                  </td>
                  <td class="px-6 py-4 text-center">
                    <button @click="removeItem(idx)" class="text-red-500 hover:text-red-700 transition p-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="mt-6 py-12 text-center text-gray-400 border-3 border-dashed border-gray-200 rounded-2xl">
            <p class="text-xl">🔍 Search and add products to return using the search box above</p>
          </div>
        </div>

        <!-- Grand Total -->
        <div class="flex justify-end pt-6 border-t-2">
          <div class="bg-red-700 text-white rounded-2xl px-12 py-6 text-right space-y-2">
            <p class="text-lg text-red-200">Total Items: <span class="font-bold text-white text-xl">{{ form.items.length }}</span></p>
            <p class="text-3xl font-bold">Total Return: Rs. {{ grandTotal.toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}</p>
          </div>
        </div>

        <!-- Validation Error -->
        <div v-if="globalError" class="bg-red-50 border-2 border-red-200 rounded-xl p-5 text-red-700 text-lg">
          {{ globalError }}
        </div>
        <p v-if="errors.items" class="text-red-500 text-lg">{{ errors.items }}</p>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4 pt-4">
          <Link href="/goods-return-notes" class="px-10 py-4 bg-gray-200 text-gray-700 rounded-xl font-bold text-lg hover:bg-gray-300 transition">
            Cancel
          </Link>
          <button
            @click="submit"
            :disabled="submitting"
            class="px-12 py-4 bg-red-600 text-white rounded-xl font-bold text-xl hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
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
      product_id:      product.id,
      product_name:    product.name,
      product_code:    product.code,
      product_barcode: product.barcode,
      current_stock:   product.stock_quantity,
      quantity:        1,
      unit_cost:       parseFloat(product.cost_price) || 0,
      notes:           '',
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
