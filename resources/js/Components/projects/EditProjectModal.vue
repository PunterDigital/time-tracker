<!-- EditProjectModal.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';

const props = defineProps({
    project: {
        type: Object,
        required: true
    },
    showModal: Boolean,
});

const emit = defineEmits(['update:showModal']);

const form = useForm({
    client: '',
    name: '',
    budget: null,
    billing_rate: null,
});

const initForm = () => {
    if (props.project) {
        form.client = props.project.client;
        form.name = props.project.name;
        form.budget = props.project.budget;
        form.billing_rate = props.project.billing_rate;
    }
};

onMounted(() => {
    initForm();
});

watch(() => props.project, (newProject) => {
    if (newProject) {
        initForm();
    }
}, { deep: true });

const updateProject = () => {
    form.put(route('projects.update', props.project.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('update:showModal', false);
        },
    });
};

const closeModal = () => {
    emit('update:showModal', false);
    form.reset();
};
</script>

<template>
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>

        <div class="bg-white p-6 rounded-md shadow-lg w-1/2 z-10">
            <h3 class="text-xl font-semibold mb-4">Edit Project</h3>
            <form @submit.prevent="updateProject" class="flex flex-col gap-5">
                <div>
                    <label for="edit-client" class="block text-sm font-semibold">Client:</label>
                    <input id="edit-client" v-model="form.client" required class="border rounded-md p-2 w-full mb-1" :class="{ 'border-red-500': form.errors.client }" />
                    <div v-if="form.errors.client" class="text-red-500 text-xs mb-2">{{ form.errors.client }}</div>
                </div>
                <div>
                    <label for="edit-name" class="block text-sm font-semibold">Project Name:</label>
                    <input id="edit-name" v-model="form.name" required class="border rounded-md p-2 w-full mb-1" :class="{ 'border-red-500': form.errors.name }" />
                    <div v-if="form.errors.name" class="text-red-500 text-xs mb-2">{{ form.errors.name }}</div>
                </div>
                <div>
                    <label for="edit-budget" class="block text-sm font-semibold">Budget:</label>
                    <input type="number" id="edit-budget" v-model="form.budget" step="0.01" class="border rounded-md p-2 w-full mb-1" :class="{ 'border-red-500': form.errors.budget }" />
                    <div v-if="form.errors.budget" class="text-red-500 text-xs mb-2">{{ form.errors.budget }}</div>
                </div>
                <div>
                    <label for="edit-billingRate" class="block text-sm font-semibold">Billing Rate per Hour:</label>
                    <input type="number" id="edit-billingRate" v-model="form.billing_rate" step="0.01" class="border rounded-md p-2 w-full mb-1" :class="{ 'border-red-500': form.errors.billing_rate }" />
                    <div v-if="form.errors.billing_rate" class="text-red-500 text-xs mb-2">{{ form.errors.billing_rate }}</div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                        {{ form.processing ? 'Updating...' : 'Update' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
