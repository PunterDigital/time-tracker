<template>
    <AppLayout title="Projects">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projects
            </h2>
        </template>
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-semibold mb-4">Projects</h1>
            <form @submit.prevent="createProject" class="bg-gray-100 p-4 rounded-md shadow-sm mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="client" class="block text-sm font-semibold">Client:</label>
                        <input id="client" v-model="newProject.client" required class="border rounded-md p-2 w-full" />
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-semibold">Project Name:</label>
                        <input id="name" v-model="newProject.name" required class="border rounded-md p-2 w-full" />
                    </div>
                    <div>
                        <label for="budget" class="block text-sm font-semibold">Budget:</label>
                        <input type="number" id="budget" v-model="newProject.budget" step="0.01" class="border rounded-md p-2 w-full" />
                    </div>
                    <div>
                        <label for="billingRate" class="block text-sm font-semibold">Billing Rate per Hour:</label>
                        <input type="number" id="billingRate" v-model="newProject.billingRate" step="0.01" class="border rounded-md p-2 w-full" />
                    </div>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mt-4">Create Project</button>
            </form>

            <!-- Edit Project Modal -->
            <div v-if="editingProject" class="fixed inset-0 flex items-center justify-center z-50">
                <div class="fixed inset-0 bg-black opacity-50"></div>

                <div class="bg-white p-6 rounded-md shadow-lg w-1/2 z-10">
                    <h3 class="text-xl font-semibold mb-4">Edit Project</h3>
                    <div>
                        <label for="edit-client" class="block text-sm font-semibold">Client:</label>
                        <input id="edit-client" v-model="editingProject.client" required class="border rounded-md p-2 w-full mb-3" />
                    </div>
                    <div>
                        <label for="edit-name" class="block text-sm font-semibold">Project Name:</label>
                        <input id="edit-name" v-model="editingProject.name" required class="border rounded-md p-2 w-full mb-3" />
                    </div>
                    <div>
                        <label for="edit-budget" class="block text-sm font-semibold">Budget:</label>
                        <input type="number" id="edit-budget" v-model="editingProject.budget" step="0.01" class="border rounded-md p-2 w-full mb-3" />
                    </div>
                    <div>
                        <label for="edit-billingRate" class="block text-sm font-semibold">Billing Rate per Hour:</label>
                        <input type="number" id="edit-billingRate" v-model="editingProject.billing_rate" step="0.01" class="border rounded-md p-2 w-full mb-3" />
                    </div>
                    <div class="flex justify-end">
                        <button @click="updateProject" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mr-2">Update</button>
                        <button @click="closeEditModal" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
                    </div>
                </div>
            </div>

            <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50">
                <div class="fixed inset-0 bg-black opacity-50"></div>

                <div class="bg-white p-6 rounded-md shadow-lg w-1/2 z-10">
                    <h3>Select Users to Add:</h3>
                    <div v-for="user in teamUsers" :key="user.id">
                        <input type="checkbox" :value="user.id" v-model="selectedUsers">
                        {{ user.name }}
                    </div>
                    <button @click="addUsersToProject">Add Users</button>
                    <button @click="showModal = false">Cancel</button>
                </div>
            </div>

            <h3 class="text-xl font-semibold mb-2">Your Projects</h3>
            <table class="min-w-full bg-white rounded-md shadow-sm">
                <thead>
                <tr class="border-b">
                    <th class="p-4 text-left">Client</th>
                    <th class="p-4 text-left">Project Name</th>
                    <th class="p-4 text-left">Total Time</th>
                    <th class="p-4 text-left" v-if="isAdmin">Budget</th>
                    <th class="p-4 text-left">Billing Rate</th>
                    <th class="p-4 text-left"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="project in projects" :key="project.id">
                    <td class="p-4">{{ project.client }}</td>
                    <td class="p-4">{{ project.name }}</td>
                    <td class="p-4">{{ formatTime(project.total_time) }}</td>
                    <td class="p-4">${{ project.budget }}</td>
                    <td class="p-4">${{ project.billing_rate }}</td>
                    <td class="p-4">
                        <button @click="showModal = true; this.projectId = project.id">Add Users to Project</button> |
                        <button @click="editProject(project.id)" class="text-blue-500 hover:text-blue-700">Edit</button> |
                        <button @click="deleteProject(project.id)" class="text-red-500 hover:text-red-700">Delete</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>


<script>
import axios from 'axios';
import AppLayout from "@/Layouts/AppLayout.vue";

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
            projects: [],
            teamUsers: [], // Fetch this list from the server
            selectedUsers: [],
            projectId: null,
        };
    },
    async created() {
        await this.fetchProjects();
        await this.fetchTeamUsers();
    },
    methods: {
        async fetchTeamUsers() {
            try {
                const response = await axios.get('/api/team-users');
                this.teamUsers = response.data;
            } catch (error) {
                console.error('An error occurred while fetching the team users:', error);
            }
        },
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
        editProject(projectId) {
            this.editingProject = this.projects.find(project => project.id === projectId);
        },
        closeEditModal() {
            this.editingProject = null;
        },
        async updateProject() {
            try {
                const response = await axios.put(`/api/projects/${this.editingProject.id}`, this.editingProject);
                if (response.status === 200) {
                    this.closeEditModal();
                    await this.fetchProjects(); // Refresh the projects list
                } else {
                    console.error('Failed to update the project:', response.data);
                }
            } catch (error) {
                console.error('An error occurred while updating the project:', error);
            }
        },
        async deleteProject(projectId) {
            if (window.confirm('Are you sure you want to delete this project?')) {
                try {
                    await axios.delete(`/api/projects/${projectId}`);
                    this.projects = this.projects.filter(project => project.id !== projectId);
                    // Optionally, show a success message
                } catch (error) {
                    console.error('An error occurred while deleting the project:', error);
                    // Optionally, show an error message
                }
            }
        },
        formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = seconds % 60;
            return `${hours}h ${minutes}m ${remainingSeconds}s`;
        },
        async createProject() {
            try {
                await axios.post('/projects', this.newProject);
                this.newProject.client = '';
                this.newProject.name = '';
                this.newProject.budget = null; // Reset budget
                await this.fetchProjects(); // Refresh the projects list
            } catch (error) {
                console.error('An error occurred while creating the project:', error);
            }
        },
        async fetchProjects() {
            try {
                const response = await axios.get('/api/projects');
                this.projects = response.data;
            } catch (error) {
                console.error('An error occurred while fetching the projects:', error);
            }
        },
    },
};
</script>
