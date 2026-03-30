
<style lang="css">
.pagination-disabled {
  color: rgb(37 99 235);
  transition: all 0.5s ease;
  background: rgb(229 231 235 / var(--tw-bg-opacity));
}
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  font-size: 14px;
  float: right;
}

.pagination a:first-child,
.pagination a:last-child {
  padding: 8px 16px;
}
</style>
<template>
  <Head title="Products and Barcodes" />
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
  >
    <!-- Include the Header -->
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-16">
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <button
            v-if="products.data.length > 0"
            @click="printAllBarcodes"
            class="flex items-center gap-3 px-6 py-3 text-white transition duration-200 bg-purple-600 rounded-xl hover:bg-purple-700 font-bold text-lg"
            title="Print all filtered product barcodes based on stock quantity"
          >
            <i class="ri-printer-line text-2xl"></i>
            <span>Print All Filtered Barcodes</span>
          </button>
        </div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{
            totalProducts
          }}</span>
          <span class="text-xl">/ Total Products</span>
        </p>
      </div>
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Products and Barcodes
          </p>
        </div>
      </div>

      <div class="flex items-center space-x-4">
        <!-- Search Input on the Left -->
        <div class="md:w-1/4 w-full">
          <input
            v-model="search"
            @input="performSearch"
            type="text"
            placeholder="Search by name or barcode..."
            class="w-full custom-input"
          />
        </div>
      </div>

      <div class="flex items-center space-x-4">
        <!-- Filter Dropdowns on the Right -->
        <div class="flex justify-end w-full space-x-2">
          <select
            v-model="selectedCategory"
            @change="applyFilters"
            class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
          >
            <option value="">Filter by Category</option>
            <option
              v-for="category in props.allcategories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>

          <!-- Supplier Filter -->
          <select
            v-model="selectedSupplier"
            @change="applyFilters"
            class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
          >
            <option value="">Filter by Supplier</option>
            <option
              v-for="supplier in props.suppliers"
              :key="supplier.id"
              :value="supplier.id"
            >
              {{ supplier.name }}
            </option>
          </select>

          <!-- Stocks Filter -->
          <select
            v-model="stockStatus"
            @change="applyFilters"
            class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
          >
            <option value="">Filter by Stock</option>
            <option value="in">In Stock</option>
            <option value="out">Out of Stock</option>
          </select>

          <!-- Price Filter -->
          <select
            v-model="sort"
            @change="applyFilters"
            class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
          >
            <option value="">Filter by Price</option>
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
          </select>

          <Link
            href="/products"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select"
          >
            Reset
          </Link>
        </div>
      </div>

      <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
        <template v-if="products.data.length > 0">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700">
              <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                  <th class="px-5 py-4 font-bold tracking-wide text-gray-800 uppercase">Name</th>
                  <th class="px-5 py-4 font-bold tracking-wide text-gray-800 uppercase">Barcode</th>
                  <th class="px-5 py-4 font-bold tracking-wide text-gray-800 uppercase">Category</th>
                  <th class="px-5 py-4 font-bold tracking-wide text-gray-800 uppercase">Supplier</th>
                  <th class="px-5 py-4 font-bold tracking-wide text-gray-800 uppercase">Price</th>
                  <th class="px-5 py-4 font-bold tracking-wide text-gray-800 uppercase">Stock</th>
                  <th class="px-5 py-4 font-bold tracking-wide text-gray-800 uppercase text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="product in products.data"
                  :key="product.id"
                  class="transition-colors border-b border-gray-100 hover:bg-blue-50"
                >
                  <td class="px-5 py-4 font-semibold text-gray-900">{{ product.name || "N/A" }}</td>
                  <td class="px-5 py-4">{{ product.barcode || "N/A" }}</td>
                  <td class="px-5 py-4">{{ product.category?.name || "N/A" }}</td>
                  <td class="px-5 py-4">{{ product.supplier?.name || "N/A" }}</td>
                  <td class="px-5 py-4 font-semibold text-emerald-700">
                    Rs. {{ Number(product.selling_price || 0).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                  </td>
                  <td class="px-5 py-4">
                    <span
                      v-if="product.stock_quantity > 0"
                      class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                    >
                      In Stock ({{ product.stock_quantity }})
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700"
                    >
                      Out of Stock
                    </span>
                  </td>
                  <td class="px-5 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        @click="openViewModal(product)"
                        class="px-3 py-2 text-xs font-semibold text-white transition bg-gray-700 rounded-lg hover:bg-gray-800"
                        title="View Product"
                      >
                        View
                      </button>
                      <button
                        @click="openBarcodeQty(product)"
                        class="flex items-center gap-1 px-3 py-2 text-xs font-semibold text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700"
                        title="Print Barcode Stickers"
                      >
                        <i class="ri-barcode-line text-base"></i>
                        <span>Print</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
        <template v-else>
          <div class="px-6 py-10 text-center text-red-500 text-[17px]">
            No Products Available
          </div>
        </template>
      </div>

      <div class="flex space-x-2 pagination">
        <!-- Prev Button -->
        <span
          v-if="products.links[0]"
          @click.prevent="navigateTo(products.links[0].url)"
          :class="[
            'pagination-btn',
            { 'pagination-disabled': !products.links[0].url },
          ]"
        >
          Previous
        </span>

        <!-- Pagination Links -->
        <span
          v-for="(link) in products.links.slice(
            1,
            products.links.length - 1
          )"
          :key="link.label"
          @click.prevent="navigateTo(link.url)"
          :class="['pagination-btn', { 'pagination-active': link.active }]"
          v-html="link.label"
        ></span>

        <!-- Next Button -->
        <span
          v-if="products.links[products.links.length - 1]"
          @click.prevent="
            navigateTo(products.links[products.links.length - 1].url)
          "
          :class="[
            'pagination-btn',
            {
              'pagination-disabled':
                !products.links[products.links.length - 1].url,
            },
          ]"
        >
          Next
        </span>
      </div>
    </div>
  </div>

  <!-- Barcode Qty Picker Modal -->
  <div
    v-if="barcodeQtyProduct"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60"
    @click.self="barcodeQtyProduct = null"
  >
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-80 text-center space-y-4">
      <div class="text-4xl">🖨</div>
      <h2 class="text-lg font-bold text-gray-800">Print Barcode Stickers</h2>
      <p class="text-sm text-gray-500 truncate">{{ barcodeQtyProduct.name }}</p>
      <p class="text-xs text-gray-400">30 mm × 16 mm · 3-column sticker roll</p>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Number of stickers</label>
        <input
          v-model.number="barcodeQty"
          type="number"
          min="1"
          max="500"
          class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-lg font-bold text-center focus:outline-none focus:border-purple-500"
        />
      </div>

      <div class="flex gap-3 pt-2">
        <button
          @click="barcodeQtyProduct = null"
          class="flex-1 border-2 border-gray-300 text-gray-600 font-semibold py-2 rounded-xl hover:bg-gray-100 transition"
        >
          Cancel
        </button>
        <button
          @click="printBarcodes"
          class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded-xl transition"
        >
          Print
        </button>
      </div>
    </div>
  </div>

  <ProductCreateModel
    :categories="allcategories"
    :suppliers="suppliers"
    v-model:open="isCreateModalOpen"
  />
  <ProductUpdateModel
    :categories="allcategories"
    :suppliers="suppliers"
    v-model:open="isEditModalOpen"
    :selected-product="selectedProduct"
  />

  <ProductDuplicateModel
    :categories="allcategories"
    :suppliers="suppliers"
    v-model:open="isDuplicateModalOpen"
    :selected-product="selectedProduct"
  />

  <ProductViewModel
    :categories="allcategories"
    v-model:open="isViewModalOpen"
    :selected-product="selectedProduct"
  />
  <ProductDeleteModel
    v-model:open="isDeleteModalOpen"
    :selected-product="selectedProduct"
    @delete="deleteProduct"
  />
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link, useForm, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { defineProps, onMounted } from "vue";
import ProductCreateModel from "@/Components/custom/ProductCreateModel.vue";

import ProductDuplicateModel from "@/Components/custom/ProductDuplicateModel.vue";
import ProductUpdateModel from "@/Components/custom/ProductUpdateModel.vue";
import ProductViewModel from "@/Components/custom/ProductViewModel.vue";
import ProductDeleteModel from "@/Components/custom/ProductDeleteModel.vue";
import { debounce } from "lodash";
import { HasRole } from "@/Utils/Permissions";

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedProduct = ref(null);
const isDeleteModalOpen = ref(false);
const barcodeQtyProduct = ref(null);   // product awaiting qty input
const barcodeQty = ref(1);             // qty for sticker print

const emit = defineEmits(["update:open"]);

const openEditModal = (product) => {
  selectedProduct.value = product; // Set the selected product
  isEditModalOpen.value = true; // Open the edit modal
};

const openDuplicateModal = (product) => {
  selectedProduct.value = product; // Set the selected product
  isDuplicateModalOpen.value = true; // Open the edit modal
};

const openViewModal = (product) => {
  selectedProduct.value = product; // Set the selected product
  isViewModalOpen.value = true; // Open the view modal
};

const openDeleteModal = (product) => {
  selectedProduct.value = product;
  isDeleteModalOpen.value = true;
};

const props = defineProps({
  products: Object,
  categories: Array,
  suppliers: Array,
  allcategories: Array,
  totalProducts: Number,
  search: String,
  sort: String,
  stockStatus: String,
  selectedCategory: String,
  selectedSupplier: String,
});

const search = ref(props.search || "");
const sort = ref(props.sort || "");
const suppliers = ref(props.suppliers || "");
const stockStatus = ref(props.stockStatus || "");
const selectedCategory = ref(props.selectedCategory || "");
const selectedSupplier = ref(props.selectedSupplier || "");

const performSearch = debounce(() => {
  applyFilters();
}, 500);

const applyFilters = (page) => {
  router.get(
    route("products.index"),
    {
      search: search.value,
      sort: sort.value,
      stockStatus: stockStatus.value,
      selectedCategory: selectedCategory.value,
      selectedSupplier: selectedSupplier.value,
    },
    { preserveState: true }
  );
};

onMounted(() => {
  // console.log("Products:", props.products);
  // console.table(props.products);
});
const showModal = ref(false);
const form = useForm({});

const openModal = (id) => {
  productToDelete.value = id;
  showModal.value = true;
};
const deleteProduct = (id) => {
  const form = useForm({});
  form.delete(`/products/${id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false; // Close the modal on success
    },
    onError: (errors) => {
      console.error("Delete failed:", errors);
    },
  });
};
const openBarcodeQty = (product) => {
  barcodeQtyProduct.value = product;
  barcodeQty.value = product.stock_quantity || 1;
};
const printBarcodes = () => {
  if (!barcodeQtyProduct.value) return;
  const qty = Math.max(1, parseInt(barcodeQty.value) || 1);
  window.open(`/barcode-sticker/${barcodeQtyProduct.value.id}?qty=${qty}`, '_blank');
  barcodeQtyProduct.value = null;
};

const printAllBarcodes = () => {
  // Build the query string with current filters
  const params = new URLSearchParams();
  if (search.value) params.append('search', search.value);
  if (sort.value) params.append('sort', sort.value);
  if (stockStatus.value) params.append('stockStatus', stockStatus.value);
  if (selectedCategory.value) params.append('selectedCategory', selectedCategory.value);
  if (selectedSupplier.value) params.append('selectedSupplier', selectedSupplier.value);

  window.open(`/barcode-sticker-bulk?${params.toString()}`, '_blank');
};

const navigateTo = (url) => {
  if (!url) return; // Avoid null or undefined URLs

  // Extract the `page` parameter from the URL
  const urlParams = new URLSearchParams(
    new URL(url, window.location.origin).search
  );
  const page = urlParams.get("page");

  // Use Inertia's router.get with current filters
  router.get(
    route("products.index"),
    {
      page, // Add the page parameter
      search: search.value,
      sort: sort.value,
      color: color.value,
      size: size.value,
      stockStatus: stockStatus.value,
      selectedCategory: selectedCategory.value,
    },
    {
      preserveState: true, // Maintain the current state
      preserveScroll: true, // Prevent scroll reset
    }
  );
};
</script>


