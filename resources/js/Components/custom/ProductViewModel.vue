<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-white text-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-6">
            <div class="flex flex-col items-start justify-start w-full h-full px-2 pt-4">
              <div class="flex justify-center w-full h-full py-4 space-x-8 items-start-center">
                <!-- Left Side: Image -->
                <div class="w-1/2">
                  <img
                    :src="selectedProduct.image ? `/${selectedProduct.image}` : '/images/placeholder.jpg'"
                    alt="Product Image"
                    class="object-cover h-full rounded-2xl"
                  />
                </div>

                <!-- Right Side: Product Details -->
                <div class="flex flex-col justify-between w-1/2 h-full">
                  <!-- Product Name & Discount Badge -->
                  <div class="flex items-center justify-between">
                    <p class="text-3xl font-bold text-black w-full break-words">
                      {{ selectedProduct.name }}
                      <span
                        v-if="selectedProduct.discount && selectedProduct.discount > 0"
                        class="inline-block px-2 py-2 text-sm font-medium text-white bg-red-600 rounded"
                      >
                        {{ selectedProduct.discount }} % OFF
                      </span>
                    </p>
                  </div>

                  <!-- Category -->
                  <p class="pb-6 mt-2 text-[#00000099] text-xl font-normal italic">
                    {{ selectedProduct.category?.name ?? "No Category" }}
                  </p>

                  <!-- Supplier -->
                  <p class="pb-6 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Supplier: </span>
                    {{ selectedProduct.supplier?.name || "N/A" }}
                  </p>

                  <!-- Product Code -->
                  <p class="pb-6 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Product Code: </span>
                    {{ selectedProduct?.code ?? "N/A" }}
                  </p>

                  <!-- Batch Number -->
                  <p class="pb-6 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Batch No: </span>
                    {{ selectedProduct?.batch_no ?? "N/A" }}
                  </p>

                  <!-- Color -->
                  <div class="flex items-center justify-between w-full text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-justify text-[#00000099] text-2xl flex items-center pb-6">
                        Color:
                        <span class="font-bold text-black">
                          {{ selectedProduct?.color?.name ?? "N/A" }}
                        </span>
                      </p>
                    </div>
                  </div>

                  <!-- Size -->
                  <div class="flex items-center justify-between w-full text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099] text-2xl pb-6">
                        Size:
                        <span class="px-2 py-2 font-bold text-black border-2 border-gray-800 rounded-xl">
                          {{ selectedProduct?.size?.name ?? "N/A" }}
                        </span>
                      </p>
                    </div>
                  </div>

                  <!-- Selling Price & Cost Price -->
                  <div class="flex items-center justify-between w-full pb-6 text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Selling Price:</p>
                      <p class="font-bold text-black">
                        {{ selectedProduct?.selling_price ?? "N/A" }} LKR
                      </p>
                    </div>
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Cost Price:</p>
                      <p class="font-bold text-black">
                        {{ selectedProduct?.cost_price ?? "N/A" }} LKR
                      </p>
                    </div>
                  </div>

                  <!-- Discount Price & Quantity -->
                  <div class="flex items-center justify-between w-full pb-6 text-2xl">
                    <div
                      class="flex flex-col w-full"
                      v-if="selectedProduct.discount && selectedProduct.discount > 0"
                    >
                      <p class="text-[#00000099]">Discount Price:</p>
                      <p class="font-bold text-black">
                        {{
                          selectedProduct.selling_price &&
                          selectedProduct.discount &&
                          selectedProduct.discount > 0
                            ? (
                                selectedProduct.selling_price -
                                (selectedProduct.selling_price * selectedProduct.discount) / 100
                              ).toFixed(2)
                            : selectedProduct.selling_price
                        }}
                        LKR
                      </p>
                    </div>
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Quantity:</p>
                      <p class="font-bold text-black">
                        {{ selectedProduct?.stock_quantity ?? "N/A" }}
                      </p>
                    </div>
                  </div>

                  <!-- Created Date -->
                  <p class="pb-8 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Created On: </span>
                    {{ formattedDate }}
                  </p>
                </div>
              </div>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import dayjs from "dayjs";
import advancedFormat from "dayjs/plugin/advancedFormat";
import { HasRole } from "@/Utils/Permissions";

dayjs.extend(advancedFormat);

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

const emit = defineEmits(["update:open"]);

const { selectedProduct } = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  categories: {
    type: Array,
    required: true,
  },
  colors: {
    type: Array,
    required: true,
  },
  sizes: {
    type: Array,
    required: true,
  },
  selectedProduct: {
    type: Object,
    default: null,
  },
});

const formattedDate = computed(() =>
  selectedProduct && selectedProduct.created_at
    ? dayjs(selectedProduct.created_at).format("Do MMMM YYYY")
    : ""
);
</script>
