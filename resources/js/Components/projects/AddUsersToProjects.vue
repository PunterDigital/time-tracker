<!-- AddUsersToProjectModal.vue -->
<template>
    <transition name="modal">
        <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>

            <div class="bg-white rounded-lg shadow-xl w-full max-w-md z-10 overflow-hidden">
                <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Select Users to Add</h3>
                </div>

                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <div v-if="teamUsers.length === 0" class="text-gray-500 text-center py-4">
                        No users available to add.
                    </div>
                    <div v-else v-for="user in teamUsers" :key="user.id" class="flex items-center py-2">
                        <input
                            type="checkbox"
                            :id="'user-' + user.id"
                            :value="user.id"
                            v-model="selectedUsers"
                            class="form-checkbox h-5 w-5 text-blue-600 transition duration-150 ease-in-out"
                        >
                        <label :for="'user-' + user.id" class="ml-3 block text-sm leading-5 text-gray-700">
                            {{ user.name }}
                        </label>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 mr-2"
                    >
                        Cancel
                    </button>
                    <button
                        @click="addUsersToProject"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        :disabled="selectedUsers.length === 0"
                    >
                        Add Users
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    showModal: Boolean,
    teamUsers: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:showModal', 'addUsers']);

const selectedUsers = ref([]);

watch(() => props.showModal, (newValue) => {
    if (newValue) {
        selectedUsers.value = [];
    }
});

const closeModal = () => {
    emit('update:showModal', false);
};

const addUsersToProject = () => {
    emit('addUsers', selectedUsers.value);
    closeModal();
};
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
</style>
