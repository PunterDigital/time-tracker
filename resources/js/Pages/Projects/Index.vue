<template>
    <AppLayout title="Projects">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projects
            </h2>

            <button @click="openModal" v-if="$can('projects:create')" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                New Project
            </button>
        </template>
        <div class="max-w-7xl mx-auto p-4">
            <table class="mt-5 min-w-full bg-white rounded-md shadow-sm">
                <thead>
                <tr class="border-b">
                    <th class="p-4 text-left">Client</th>
                    <th class="p-4 text-left">Project Name</th>
                    <th class="p-4 text-left">Total Time</th>
                    <th class="p-4 text-left" v-if="$hasRole('admin')">Budget</th>
                    <th class="p-4 text-left">Billing Rate</th>
                    <th class="p-4 text-left"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="project in projects" :key="project.id">
                    <td class="p-4">{{ project.client }}</td>
                    <td class="p-4">{{ project.name }}</td>
                    <td class="p-4">{{ formatTime(project.total_time) }}</td>
                    <td class="p-4" v-if="$hasRole('admin')">${{ project.budget }}</td>
                    <td class="p-4">${{ project.billing_rate }}</td>
                    <td class="p-4 flex flex-row gap-2">
                        <button @click="isAddUsersModalOpen = true" type="button" class="rounded bg-blue-600 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Add User to Project</button>

                        <button @click="openEditModal(project)" type="button" class="rounded bg-purple-600 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600">Edit Project</button>

                        <button @click="deleteProject(project)" type="button" class="rounded bg-red-600 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Delete Project</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <NewProjectModal v-model:isOpen="isModalOpen" />

        <EditProjectModal
            :project="selectedProject"
            v-model:showModal="isEditModalOpen"
        />

        <AddUsersToProjects
            v-model:showModal="isAddUsersModalOpen"
            :teamUsers="availableUsers"
            @addUsers="handleAddUsers"
        />

    </AppLayout>
</template>

<script setup>
import {ref} from "vue";
import NewProjectModal from "@/Components/projects/NewProjectModal.vue";
import EditProjectModal from "@/Components/projects/EditProjectModal.vue";
import {usePage} from "@inertiajs/vue3";
import AddUsersToProjects from "@/Components/projects/AddUsersToProjects.vue";

const props = defineProps({
    projects: {
        type: Array,
        required: true,
    },
    teamUsers: {
        type: Array,
        required: true,
    },
});

const isModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedProject = ref(null);
const isAddUsersModalOpen = ref(false);
const availableUsers = ref(props.teamUsers); // Populate this with your team users

const handleAddUsers = (selectedUserIds) => {
    // Handle adding the selected users to your project
    console.log('Selected user IDs:', selectedUserIds);
    // Your logic to add users to the project
};

const openEditModal = (project) => {
    selectedProject.value = project;
    isEditModalOpen.value = true;
};

const openModal = () => {
    isModalOpen.value = true;
};

const page = usePage();

const deleteProject = (project) => {
    if (window.confirm('Are you sure you want to delete this project?')) {
        router.delete(`/projects/${project.id}`);
    }
}
</script>


<script>
import axios from 'axios';
import AppLayout from "@/Layouts/AppLayout.vue";
import {router} from "@inertiajs/vue3";

export default {
    components: {AppLayout},
    data() {
        return {
            newProject: {
                client: '',
                name: '',
                budget: 0.0, // Add budget property
                billingRate: null, // Add billing rate property
            },
            showModal: false,
            editingProject: null,
            selectedUsers: [],
            projectId: null,
        };
    },
    methods: {
        async addUsersToProject() {
            try {
                await axios.post(`/api/projects/${this.projectId}/add-users`, {
                    users: this.selectedUsers,
                });
                // Refresh the project's users or navigate to a new page
            } catch (error) {
                console.error('An error occurred while adding users:', error);
            }
            this.showModal = false;
        },
        formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = seconds % 60;
            return `${hours}h ${minutes}m ${remainingSeconds}s`;
        },
    },
};
</script>
