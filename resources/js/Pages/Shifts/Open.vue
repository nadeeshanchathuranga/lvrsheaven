<template>
  <Head title="Open Shift" />
  <Banner />
  <div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center px-4">

    <div class="w-full max-w-md">
      <!-- Logo / Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-600 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">Open Your Shift</h1>
        <p class="text-gray-400 mt-1">Count your opening cash and start your shift to use the POS.</p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-2xl p-8">
        <div class="mb-5 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-sm">
          <strong>⚠ Important:</strong> You cannot use the POS without an open shift. Enter the cash amount currently in the till drawer.
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <!-- Cashier info -->
          <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
              {{ initials }}
            </div>
            <div>
              <p class="font-semibold text-gray-800">{{ $page.props.auth.user.name }}</p>
              <p class="text-xs text-gray-500">{{ $page.props.auth.user.role_type }}</p>
            </div>
          </div>

          <!-- Opening Float -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Opening Cash in Till (Rs.) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.opening_float"
              type="number"
              min="0"
              step="0.01"
              placeholder="0.00"
              required
              class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-lg font-semibold focus:outline-none focus:border-green-500 text-gray-800"
            />
            <p v-if="errors.opening_float" class="text-red-500 text-xs mt-1">{{ errors.opening_float }}</p>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Notes (optional)</label>
            <input
              v-model="form.notes"
              type="text"
              placeholder="Any notes for this shift..."
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-green-500 text-gray-700"
            />
          </div>

          <button
            type="submit"
            :disabled="submitting"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl text-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="submitting">Opening shift...</span>
            <span v-else>🟢 Open Shift & Go to POS</span>
          </button>
        </form>
      </div>

      <!-- Date & time -->
      <p class="text-center text-gray-500 text-sm mt-4">{{ currentDateTime }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';

const page = usePage();
const errors = computed(() => page.props.errors ?? {});

const form = ref({
  opening_float: '',
  notes: '',
});
const submitting = ref(false);

const initials = computed(() => {
  const name = page.props.auth?.user?.name ?? '';
  return name.slice(0, 2).toUpperCase();
});

const currentDateTime = ref('');
let timer = null;
const updateTime = () => {
  currentDateTime.value = new Date().toLocaleString('en-LK', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit', second: '2-digit',
  });
};
onMounted(() => { updateTime(); timer = setInterval(updateTime, 1000); });
onUnmounted(() => clearInterval(timer));

const submit = () => {
  submitting.value = true;
  router.post('/shifts/start', {
    opening_float: form.value.opening_float,
    notes: form.value.notes,
  }, {
    onError: () => { submitting.value = false; },
    onFinish: () => { submitting.value = false; },
  });
};
</script>
