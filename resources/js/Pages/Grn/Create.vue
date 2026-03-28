<template>
  <Head title="Create GRN" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 bg-gray-100 md:px-36 px-4">
    <Header />

    <div class="w-full md:w-5/6 mt-8 space-y-6">
      <!-- Title -->
      <div class="flex items-center space-x-6">
        <Link href="/grn">
          <img src="/images/back-arrow.png" class="w-16 h-16" />
        </Link>
        <p class="text-5xl font-bold tracking-wide text-black uppercase">New GRN (Goods Received Note)</p>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl shadow-lg p-8 space-y-8">
        <!-- Header Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Supplier -->
          <div>
            <label class="block text-lg font-bold text-gray-700 mb-3">Supplier <span class="text-red-500">*</span></label>
            <select
              v-model="form.supplier_id"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-blue-400 text-lg"
            >
              <option value="">Select supplier...</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <p v-if="errors.supplier_id" class="text-red-500 text-base mt-2">{{ errors.supplier_id }}</p>
          </div>

          <!-- GRN Date -->
          <div>
            <label class="block text-lg font-bold text-gray-700 mb-3">GRN Date <span class="text-red-500">*</span></label>
            <input
              v-model="form.grn_date"
              type="date"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-blue-400 text-lg"
            />
            <p v-if="errors.grn_date" class="text-red-500 text-base mt-2">{{ errors.grn_date }}</p>
          </div>

          <!-- Reference No -->
          <div>
            <label class="block text-lg font-bold text-gray-700 mb-3">Supplier Invoice / Ref No.</label>
            <input
              v-model="form.reference_no"
              type="text"
              placeholder="Supplier's invoice number"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-blue-400 text-lg"
            />
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-lg font-bold text-gray-700 mb-3">Notes</label>
            <input
              v-model="form.notes"
              type="text"
              placeholder="Optional notes..."
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-blue-400 text-lg"
            />
          </div>
        </div>

        <!-- Product Search & Add -->
        <div class="border-t-2 pt-6">
          <h3 class="text-2xl font-bold text-gray-700 mb-5">Add Products</h3>
          <div class="flex flex-wrap gap-4 items-end mb-6">
            <div class="flex-1 min-w-64 relative">
              <label class="block text-xl font-bold text-gray-700 mb-3">Search Existing Product (Barcode/Name/Code)</label>
              <input
                v-model="productSearch"
                @input="searchProducts"
                @keydown.enter.prevent="handleBarcodeEnter"
                type="text"
                placeholder="Scan barcode or type to search..."
                class="w-full border-3 border-gray-400 rounded-xl px-6 py-5 focus:outline-none focus:ring-3 focus:ring-blue-400 text-xl font-medium"
                ref="searchInput"
              />
              <!-- Dropdown -->
              <div
                v-if="searchResults.length > 0"
                class="absolute z-50 top-full mt-2 left-0 w-full bg-white border-2 border-gray-300 rounded-xl shadow-xl max-h-80 overflow-y-auto"
              >
                <div
                  v-for="p in searchResults"
                  :key="p.id"
                  @click="selectExistingProduct(p)"
                  class="px-6 py-4 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0"
                >
                  <p class="font-bold text-lg text-gray-800">{{ p.name }}</p>
                  <p class="text-base text-gray-400 mt-1">Code: {{ p.code || 'N/A' }} | Barcode: {{ p.barcode || 'N/A' }} | Stock: {{ p.stock_quantity }} | Cost: Rs. {{ p.cost_price }}</p>
                </div>
              </div>
              <p v-if="searching" class="text-base text-gray-400 mt-2">Searching...</p>
            </div>
          </div>

          <!-- Add New Product Inline Form -->
          <div class="bg-green-50 border-3 border-green-400 rounded-2xl p-8 mb-8">
            <h4 class="text-2xl font-bold text-green-800 mb-6">➕ Or Add New Product</h4>
            <div class="grid grid-cols-1 md:grid-cols-7 gap-6 items-end">
              <div class="md:col-span-2">
                <label class="block text-lg font-bold text-gray-700 mb-3">Product Name <span class="text-red-500">*</span></label>
                <input
                  v-model="newProduct.name"
                  type="text"
                  placeholder="Enter product name"
                  class="w-full border-2 border-gray-300 rounded-xl px-5 py-4 text-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                />
                <p v-if="newProductErrors.name" class="text-red-500 text-base mt-2">{{ newProductErrors.name }}</p>
              </div>

              <div>
                <label class="block text-lg font-bold text-gray-700 mb-3">Category</label>
                <div v-if="!showNewCategoryInput">
                  <select
                    v-model="newProduct.category_id"
                    class="w-full border-2 border-gray-300 rounded-xl px-5 py-4 text-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                  >
                    <option value="">Select...</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                  <button
                    @click="showNewCategoryInput = true"
                    type="button"
                    class="mt-2 text-green-700 text-sm font-semibold hover:underline"
                  >
                    + Create New Category
                  </button>
                </div>
                <div v-else>
                  <input
                    v-model="newProduct.new_category_name"
                    type="text"
                    placeholder="New category name"
                    class="w-full border-2 border-green-400 rounded-xl px-5 py-4 text-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                  />
                  <button
                    @click="cancelNewCategory"
                    type="button"
                    class="mt-2 text-gray-600 text-sm hover:underline"
                  >
                    ← Back to Select
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-lg font-bold text-gray-700 mb-3">Barcode (12 digits)</label>
                <input
                  v-model="newProduct.barcode"
                  type="text"
                  maxlength="12"
                  placeholder="Auto-generated"
                  class="w-full border-2 border-gray-300 rounded-xl px-5 py-4 text-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                />
              </div>

              <div>
                <label class="block text-lg font-bold text-gray-700 mb-3">Cost Price <span class="text-red-500">*</span></label>
                <input
                  v-model.number="newProduct.unit_cost"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                  class="w-full border-2 border-gray-300 rounded-xl px-5 py-4 text-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                />
                <p v-if="newProductErrors.unit_cost" class="text-red-500 text-base mt-2">{{ newProductErrors.unit_cost }}</p>
              </div>

              <div>
                <label class="block text-lg font-bold text-gray-700 mb-3">Selling Price <span class="text-red-500">*</span></label>
                <input
                  v-model.number="newProduct.selling_price"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                  class="w-full border-2 border-gray-300 rounded-xl px-5 py-4 text-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                />
                <p v-if="newProductErrors.selling_price" class="text-red-500 text-base mt-2">{{ newProductErrors.selling_price }}</p>
              </div>

              <div>
                <label class="block text-lg font-bold text-gray-700 mb-3">Quantity <span class="text-red-500">*</span></label>
                <input
                  v-model.number="newProduct.quantity"
                  type="number"
                  min="1"
                  placeholder="1"
                  class="w-full border-2 border-gray-300 rounded-xl px-5 py-4 text-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                />
                <p v-if="newProductErrors.quantity" class="text-red-500 text-base mt-2">{{ newProductErrors.quantity }}</p>
              </div>

              <button
                @click="addNewProduct"
                class="px-8 py-4 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition text-lg"
              >
                Add Product
              </button>
            </div>
          </div>

          <!-- Items Table -->
          <div class="mt-8 overflow-x-auto" v-if="form.items.length > 0">
            <table class="w-full text-lg border-collapse">
              <thead>
                <tr class="bg-gray-800 text-white">
                  <th class="px-6 py-5 text-left text-xl">#</th>
                  <th class="px-6 py-5 text-left text-xl">Product</th>
                  <th class="px-6 py-5 text-left text-xl">Category</th>
                  <th class="px-6 py-5 text-left text-xl">Barcode</th>
                  <th class="px-6 py-5 text-left text-xl">Current Stock</th>
                  <th class="px-6 py-5 text-center text-xl">Qty Received</th>
                  <th class="px-6 py-5 text-center text-xl">Unit Cost (Rs.)</th>
                  <th class="px-6 py-5 text-right text-xl">Total</th>
                  <th class="px-6 py-5 text-center text-xl">Remove</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, idx) in form.items"
                  :key="idx"
                  class="border-b-2 border-gray-100 hover:bg-gray-50"
                >
                  <td class="px-6 py-4 text-gray-500 text-lg">{{ idx + 1 }}</td>
                  <td class="px-6 py-4">
                    <p class="font-bold text-xl text-gray-800">{{ item.product_name || item.name }}</p>
                    <p class="text-base text-gray-400">{{ item.product_code || item.code || 'New Product' }}</p>
                    <span v-if="item.is_new_product" class="inline-block mt-1 px-3 py-1 bg-green-100 text-green-700 text-sm font-semibold rounded">NEW</span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-block px-3 py-1 bg-purple-100 text-purple-700 text-sm font-semibold rounded">
                      {{ getCategoryDisplay(item) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-600 text-lg font-mono">{{ item.barcode || 'Auto-generated' }}</td>
                  <td class="px-6 py-4 text-gray-500 text-lg font-semibold">{{ item.current_stock || 0 }}</td>
                  <td class="px-6 py-4">
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      @input="recalculate"
                      class="w-32 border-2 border-gray-300 rounded-xl px-4 py-3 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="itemErrors[idx]?.quantity" class="text-red-500 text-base mt-2">{{ itemErrors[idx].quantity }}</p>
                  </td>
                  <td class="px-6 py-4">
                    <input
                      v-model.number="item.unit_cost"
                      type="number"
                      min="0"
                      step="0.01"
                      @input="recalculate"
                      class="w-40 border-2 border-gray-300 rounded-xl px-4 py-3 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="itemErrors[idx]?.unit_cost" class="text-red-500 text-base mt-2">{{ itemErrors[idx].unit_cost }}</p>
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
            <p class="text-xl">🔍 Search and add existing products or click "Add New Product" to create new items</p>
          </div>
        </div>

        <!-- Grand Total -->
        <div class="flex justify-end pt-6 border-t-2">
          <div class="bg-gray-800 text-white rounded-2xl px-12 py-6 text-right space-y-2">
            <p class="text-lg text-gray-300">Total Items: <span class="font-bold text-white text-xl">{{ form.items.length }}</span></p>
            <p class="text-3xl font-bold">Grand Total: Rs. {{ grandTotal.toLocaleString('en-LK', { minimumFractionDigits: 2 }) }}</p>
          </div>
        </div>

        <!-- Validation Error Summary -->
        <div v-if="globalError" class="bg-red-50 border-2 border-red-200 rounded-xl p-5 text-red-700 text-lg">
          {{ globalError }}
        </div>
        <p v-if="errors.items" class="text-red-500 text-lg">{{ errors.items }}</p>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4 pt-4">
          <Link href="/grn" class="px-10 py-4 bg-gray-200 text-gray-700 rounded-xl font-bold text-lg hover:bg-gray-300 transition">
            Cancel
          </Link>
          <button
            @click="submit"
            :disabled="submitting"
            class="px-12 py-4 bg-blue-600 text-white rounded-xl font-bold text-xl hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="submitting">Saving GRN...</span>
            <span v-else">Save GRN</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
  suppliers: Array,
  categories: Array,
});

const page = usePage();
const errors = computed(() => page.props.errors ?? {});

const today = new Date().toISOString().slice(0, 10);

const form = reactive({
  supplier_id: '',
  grn_date: today,
  reference_no: '',
  notes: '',
  items: [],
});

const productSearch = ref('');
const searchResults = ref([]);
const searching = ref(false);
const submitting = ref(false);
const globalError = ref('');
const itemErrors = reactive({});
const searchInput = ref(null);

const newProduct = reactive({
  name: '',
  category_id: '',
  new_category_name: '',
  barcode: '',
  unit_cost: null,
  selling_price: null,
  quantity: 1,
});
const newProductErrors = reactive({});
const showNewCategoryInput = ref(false);

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
      const res = await fetch('/api/grn/search-product', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ search: productSearch.value }),
      });
      const data = await res.json();
      searchResults.value = data.products ?? [];
    } catch (err) {
      console.error('Search error:', err);
      searchResults.value = [];
    } finally {
      searching.value = false;
    }
  }, 350);
};

const handleBarcodeEnter = () => {
  if (searchResults.value.length === 1) {
    selectExistingProduct(searchResults.value[0]);
  }
};

const generateBatchNo = () => {
  const d = new Date();
  const ymd = d.getFullYear().toString() +
    String(d.getMonth() + 1).padStart(2, '0') +
    String(d.getDate()).padStart(2, '0');
  const rand = String(Math.floor(Math.random() * 9000) + 1000);
  return `BAT-${ymd}-${rand}`;
};

// Generate 12-digit Sri Lankan barcode format:
// XXX-CCCC-PPPP-C
// First 3 digits: prefix (usually 955 for Sri Lanka)
// Next 4 digits: encoded cost price (scaled and padded)
// Next 4 digits: random product identifier
// Last digit: check digit
const generateBarcode = (costPrice) => {
  const prefix = '955'; // Sri Lankan prefix
  
  // Encode cost in 4 digits (scale up and limit to 4 digits)
  // For example: cost 1234.56 -> 1235, cost 45.80 -> 0046
  const costEncoded = String(Math.round(costPrice)).padStart(4, '0').slice(-4);
  
  // Random 4-digit product identifier
  const productId = String(Math.floor(Math.random() * 10000)).padStart(4, '0');
  
  // Calculate check digit (simple modulo 10)
  const digits = prefix + costEncoded + productId;
  let sum = 0;
  for (let i = 0; i < digits.length; i++) {
    sum += parseInt(digits[i]) * (i % 2 === 0 ? 1 : 3);
  }
  const checkDigit = (10 - (sum % 10)) % 10;
  
  return prefix + costEncoded + productId + checkDigit;
};

const selectExistingProduct = (product) => {
  const existing = form.items.find((i) => i.product_id === product.id);
  if (existing) {
    existing.quantity += 1;
  } else {
    form.items.push({
      product_id: product.id,
      product_name: product.name,
      product_code: product.code,
      product_category: product.category?.name,
      barcode: product.barcode,
      current_stock: product.stock_quantity,
      quantity: 1,
      unit_cost: parseFloat(product.cost_price) || 0,
      is_new_product: false,
    });
  }
  productSearch.value = '';
  searchResults.value = [];
};

const getCategoryDisplay = (item) => {
  if (item.is_new_product) {
    // For new products
    if (item.new_category_name) {
      return item.new_category_name + ' (New)';
    } else if (item.category_id) {
      const cat = props.categories.find(c => c.id === item.category_id);
      return cat ? cat.name : 'Uncategorized';
    }
    return 'Uncategorized';
  } else {
    // For existing products
    return item.product_category || 'Uncategorized';
  }
};

const cancelNewCategory = () => {
  showNewCategoryInput.value = false;
  newProduct.new_category_name = '';
};

const addNewProduct = () => {
  // Validate
  Object.keys(newProductErrors).forEach(key => delete newProductErrors[key]);
  let valid = true;

  if (!newProduct.name || !newProduct.name.trim()) {
    newProductErrors.name = 'Required';
    valid = false;
  }
  if (!newProduct.unit_cost || newProduct.unit_cost <= 0) {
    newProductErrors.unit_cost = 'Required';
    valid = false;
  }
  if (!newProduct.selling_price || newProduct.selling_price <= 0) {
    newProductErrors.selling_price = 'Required';
    valid = false;
  }
  if (!newProduct.quantity || newProduct.quantity < 1) {
    newProductErrors.quantity = 'Required';
    valid = false;
  }

  if (!valid) return;

  // Generate barcode if not provided, encoding the cost price
  const barcode = newProduct.barcode || generateBarcode(newProduct.unit_cost);

  // Add to items list
  form.items.push({
    is_new_product: true,
    name: newProduct.name,
    category_id: newProduct.category_id || null,
    new_category_name: showNewCategoryInput.value ? newProduct.new_category_name : null,
    barcode: barcode,
    unit_cost: newProduct.unit_cost,
    selling_price: newProduct.selling_price,
    quantity: newProduct.quantity,
    current_stock: 0,
  });

  // Reset form
  Object.assign(newProduct, {
    name: '',
    category_id: '',
    new_category_name: '',
    barcode: '',
    unit_cost: null,
    selling_price: null,
    quantity: 1,
  });
  showNewCategoryInput.value = false;
  
  productSearch.value = '';
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

const recalculate = () => {};

const validate = () => {
  globalError.value = '';
  let valid = true;

  if (!form.supplier_id) {
    globalError.value = 'Please select a supplier.';
    return false;
  }
  if (!form.grn_date) {
    globalError.value = 'Please select a GRN date.';
    return false;
  }
  if (form.items.length === 0) {
    globalError.value = 'Please add at least one product.';
    return false;
  }

  form.items.forEach((item, idx) => {
    itemErrors[idx] = {};
    if (!item.quantity || item.quantity < 1) {
      itemErrors[idx].quantity = 'Min 1';
      valid = false;
    }
    if (!item.unit_cost && item.unit_cost !== 0) {
      itemErrors[idx].unit_cost = 'Required';
      valid = false;
    }
  });

  return valid;
};

const submit = () => {
  if (!validate()) return;
  submitting.value = true;

  const payload = {
    supplier_id: form.supplier_id,
    grn_date: form.grn_date,
    reference_no: form.reference_no,
    notes: form.notes,
    items: form.items.map((i) => {
      if (i.is_new_product) {
        return {
          is_new_product: true,
          name: i.name,
          category_id: i.category_id || null,
          new_category_name: i.new_category_name || null,
          barcode: i.barcode || null,
          selling_price: i.selling_price,
          quantity: i.quantity,
          unit_cost: i.unit_cost,
        };
      } else {
        return {
          product_id: i.product_id,
          quantity: i.quantity,
          unit_cost: i.unit_cost,
        };
      }
    }),
  };

  router.post('/grn', payload, {
    onFinish: () => { submitting.value = false; },
    onError: () => { submitting.value = false; },
  });
};

onMounted(() => {
  // Focus search input for quick barcode scanning
  if (searchInput.value) {
    searchInput.value.focus();
  }
});
</script>
