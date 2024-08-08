<!-- ProjectFormModal.vue -->
<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-2xl">
            <h2 class="text-2xl font-bold mb-4">Create New Project</h2>
            <form @submit.prevent="createProject" class="space-y-4">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="client" class="block text-sm font-semibold">Client:</label>
                        <input id="client" v-model="form.client" required class="border rounded-md p-2 w-full" :class="{ 'border-red-500': form.errors.client }" />
                        <div v-if="form.errors.client" class="text-red-500 text-xs mt-1">{{ form.errors.client }}</div>
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-semibold">Project Name:</label>
                        <input id="name" v-model="form.name" required class="border rounded-md p-2 w-full" :class="{ 'border-red-500': form.errors.name }" />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label for="budget" class="block text-sm font-semibold">Budget:</label>
                        <input type="number" id="budget" v-model="form.budget" step="0.01" class="border rounded-md p-2 w-full" :class="{ 'border-red-500': form.errors.budget }" />
                        <div v-if="form.errors.budget" class="text-red-500 text-xs mt-1">{{ form.errors.budget }}</div>
                    </div>
                    <div>
                        <label for="billingRate" class="block text-sm font-semibold">Billing Rate per Hour:</label>
                        <input type="number" id="billingRate" v-model="form.billing_rate" step="0.01" class="border rounded-md p-2 w-full" :class="{ 'border-red-500': form.errors.billing_rate }" />
                        <div v-if="form.errors.billing_rate" class="text-red-500 text-xs mt-1">{{ form.errors.billing_rate }}</div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="closeModal" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                        {{ form.processing ? 'Creating...' : 'Create Project' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    isOpen: Boolean,
});

const emit = defineEmits(['update:isOpen']);

const form = useForm({
    client: '',
    name: '',
    budget: null,
    billing_rate: null
});

const createProject = () => {
    form.post(route('projects.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('update:isOpen', false);
            form.reset();
        },
    });
};

const closeModal = () => {
    emit('update:isOpen', false);
    form.reset();
};
</script>
