<style>
/* General DataTables Pagination Container Style */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}
#SupplierTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}
#SupplierTable_filter label {
  font-size: 17px;
  color: #000000;
  display: flex;
  align-items: center;
}
#SupplierTable_filter input[type="search"] {
  font-weight: 400;
  padding: 9px 15px;
  font-size: 14px;
  color: #000000cc;
  border: 1px solid rgb(209 213 219);
  border-radius: 5px;
  background: #fff;
  outline: none;
  transition: all 0.5s ease;
}
#SupplierTable_filter input[type="search"]:focus {
  outline: none;
  border: 1px solid #4b5563;
  box-shadow: none;
}
#SupplierTable_filter { float: left; }
.dataTables_wrapper { margin-bottom: 10px; }
</style>

<template>
  <Head title="Suppliers" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-4">
    <Header />
    <div class="w-full md:w-5/6 py-6 space-y-8">

      <!-- Title Row -->
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Suppliers</p>
        </div>
        <div class="flex gap-3 flex-wrap">
          <Link href="/grn" class="px-6 py-3 text-lg font-bold text-white bg-green-600 rounded-xl hover:bg-green-700 transition">
            <i class="ri-file-list-3-line pr-2"></i> GRN
          </Link>
          <button
            @click="() => { if (HasRole([`Admin`])) isCreateModalOpen = true; }"
            :class="HasRole([`Admin`]) ? `px-6 py-3 text-lg font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition cursor-pointer` : `px-6 py-3 text-lg font-bold text-white bg-blue-400 rounded-xl cursor-not-allowed`"
            :disabled="!HasRole([`Admin`])"
          >
            <i class="ri-add-circle-fill pr-2"></i> Add Supplier
          </button>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-blue-500">
          <p class="text-xs text-gray-400 uppercase font-semibold">Total Suppliers</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">{{ allsuppliers.length }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-purple-500">
          <p class="text-xs text-gray-400 uppercase font-semibold">Total Purchases</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">{{ formatCurrency(totalPurchases) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
          <p class="text-xs text-gray-400 uppercase font-semibold">Total Paid</p>
          <p class="text-2xl font-bold text-green-700 mt-1">{{ formatCurrency(totalPaid) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-red-500">
          <p class="text-xs text-gray-400 uppercase font-semibold">Total Outstanding</p>
          <p class="text-2xl font-bold text-red-600 mt-1">{{ formatCurrency(totalOutstanding) }}</p>
        </div>
      </div>

      <template v-if="allsuppliers && allsuppliers.length > 0">
        <div class="overflow-x-auto">
          <table id="SupplierTable" class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
            <thead>
              <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[13px] text-white border-b border-blue-700">
                <th class="p-4 font-semibold tracking-wide text-left uppercase">Name</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase">Contact</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase">Image</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase">Email</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase">Address</th>
                <th class="p-4 font-semibold tracking-wide text-right uppercase">Total Purchases</th>
                <th class="p-4 font-semibold tracking-wide text-right uppercase">Total Paid</th>
                <th class="p-4 font-semibold tracking-wide text-right uppercase">Outstanding</th>
                <th class="p-4 font-semibold tracking-wide text-center uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="text-[13px] font-normal">
              <tr v-for="supplier in allsuppliers" :key="supplier.id" class="transition duration-200 ease-in-out hover:bg-gray-50 hover:shadow-lg">
                <td class="p-4 font-bold border-t border-gray-200">{{ supplier.name || "N/A" }}</td>
                <td class="p-4 border-t border-gray-200">{{ supplier.contact || "N/A" }}</td>
                <td class="p-4 border-t border-gray-200">
                  <img :src="supplier.image ? `/${supplier.image}` : `/images/placeholder.jpg`" alt="Supplier Image" class="object-cover rounded-md shadow h-12 w-12" />
                </td>
                <td class="p-4 border-t border-gray-200">{{ supplier.email || "N/A" }}</td>
                <td class="p-4 border-t border-gray-200">{{ supplier.address || "N/A" }}</td>
                <td class="p-4 border-t border-gray-200 text-right font-semibold text-gray-700">{{ formatCurrency(supplier.total_purchases ?? 0) }}</td>
                <td class="p-4 border-t border-gray-200 text-right font-semibold text-green-700">{{ formatCurrency(supplier.total_paid ?? 0) }}</td>
                <td class="p-4 border-t border-gray-200 text-right font-bold" :class="(supplier.outstanding ?? 0) > 0 ? `text-red-600` : `text-green-600`">
                  {{ formatCurrency(supplier.outstanding ?? 0) }}
                </td>
                <td class="p-4 text-center border-t border-gray-200">
                  <div class="inline-flex items-center space-x-2">
                    <Link :href="`/grn?supplier_id=${supplier.id}`" class="px-3 py-1 bg-green-500 text-white rounded-lg text-xs hover:bg-green-600 transition">GRNs</Link>
                    <button
                      :class="HasRole([`Admin`]) ? `px-3 py-1 bg-blue-500 text-white rounded-lg text-xs` : `px-3 py-1 bg-blue-300 text-white rounded-lg text-xs cursor-not-allowed`"
                      :disabled="!HasRole([`Admin`])"
                      @click="() => { if (HasRole([`Admin`])) openEditModal(supplier); }"
                    >Edit</button>
                    <button
                      :class="HasRole([`Admin`]) ? `px-3 py-1 bg-red-500 text-white rounded-lg text-xs` : `px-3 py-1 bg-red-300 text-white rounded-lg text-xs cursor-not-allowed`"
                      :disabled="!HasRole([`Admin`])"
                      @click="() => { if (HasRole([`Admin`])) openDeleteModal(supplier); }"
                    >Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
      <template v-else>
        <div class="col-span-4 text-center">
          <p class="text-center text-red-500 text-[17px]">No Suppliers Available</p>
        </div>
      </template>
    </div>
  </div>
  <SupplierCreateModel :suppliers="allsuppliers" v-model:open="isCreateModalOpen" />
  <SupplierDeleteModel :suppliers="allsuppliers" :selected-supplier="selectedSupplier" v-model:open="isDeleteModalOpen" />
  <SupplierUpdateModel :suppliers="allsuppliers" v-model:open="isEditModalOpen" :selected-supplier="selectedSupplier" />
  <Footer />
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import SupplierCreateModel from "@/Components/custom/SupplierCreateModel.vue";
import SupplierDeleteModel from "@/Components/custom/SupplierDeleteModel.vue";
import SupplierUpdateModel from "@/Components/custom/SupplierUpdateModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

const props = defineProps({
  allsuppliers: Array,
  totalSuppliers: Number,
});

const totalPurchases = computed(() => props.allsuppliers.reduce((sum, s) => sum + (s.total_purchases ?? 0), 0));
const totalPaid = computed(() => props.allsuppliers.reduce((sum, s) => sum + (s.total_paid ?? 0), 0));
const totalOutstanding = computed(() => props.allsuppliers.reduce((sum, s) => sum + (s.outstanding ?? 0), 0));

const formatCurrency = (val) =>
  'Rs. ' + parseFloat(val ?? 0).toLocaleString('en-LK', { minimumFractionDigits: 2 });

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedSupplier = ref(null);

const openEditModal = (supplier) => {
  selectedSupplier.value = supplier;
  isEditModalOpen.value = true;
};
const openDeleteModal = (supplier) => {
  selectedSupplier.value = supplier;
  isDeleteModalOpen.value = true;
};

$(document).ready(function () {
  $("#SupplierTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    columnDefs: [{ targets: [2, 5, 6, 7, 8], searchable: false, orderable: false }],
    initComplete: function () {
      let searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.off("keyup");
    },
    language: { search: "" },
  });
});
</script>
