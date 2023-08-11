<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import TimeTracker from "@/Components/TimeTracker.vue";
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-semibold mb-4">Time Tracking</h1>
            <div v-if="tracking.isTracking" class="bg-blue-100 p-4 rounded-md shadow-sm">
                <p class="mb-2">
                    <span class="font-semibold">Tracking project:</span> {{ currentProjectName }}<br />
                    <span class="font-semibold">Time tracked:</span> {{ currentTimeTracked }}
                </p>
                <button @click="stopTrackingTime" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                    Stop Tracking
                </button>
            </div>
            <div v-else class="bg-gray-100 p-4 rounded-md shadow-sm mt-4">
                <div class="mb-3">
                    <select v-model="selectedProject" @change="onProjectChange" class="border rounded-md p-2 w-full">
                        <option value="" disabled>Select a project</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">
                            {{ project.client }} - {{ project.name }}
                        </option>
                    </select>
                </div>
                <div class="flex items-center mb-3">
                    <input type="checkbox" v-model="isBillable" class="mr-2" />
                    <label>Mark as Billable</label>
                </div>
                <button v-if="!isTracking" @click="startTrackingTime" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                    Start Tracking
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import axios from 'axios';
import { mapState, mapMutations } from 'vuex';

export default {
    data() {
        return {
            projects: [],
            selectedProject: '',
            isBillable: true,
            currentTimeTracked: '0h 0m 0s',
        };
    },
    computed: {
        ...mapState({
            tracking: (state) => state.tracking,
        }),
        currentProjectName() {
            return this.tracking.selectedProject
                ? `${this.tracking.selectedProject.client} - ${this.tracking.selectedProject.name}`
                : '';
        },
    },
    watch: {
        'tracking.isTracking': {
            handler: function (isTracking) {
                if (isTracking) {
                    this.startTimer();
                } else {
                    this.stopTimer();
                }
            },
            immediate: true,
        },
    },
    async created() {
        this.$store.commit('loadTrackingFromStorage');
        await this.fetchProjects();
    },
    methods: {
        ...mapMutations(['startTracking', 'stopTracking']),
        startTimer() {
            this.timer = setInterval(() => {
                if (this.tracking.isTracking) {
                    const startTime = new Date(this.tracking.startTime);
                    const currentTime = new Date();
                    const durationInSeconds = (currentTime - startTime) / 1000;
                    const hours = Math.floor(durationInSeconds / 3600);
                    const minutes = Math.floor((durationInSeconds % 3600) / 60);
                    const seconds = Math.floor(durationInSeconds % 60);
                    this.currentTimeTracked = `${hours}h ${minutes}m ${seconds}s`;
                }
            }, 1000);
        },
        stopTimer() {
            clearInterval(this.timer);
            this.currentTimeTracked = '0h 0m 0s';
        },
        async fetchProjects() {
            try {
                const response = await axios.get('/api/projects');
                this.projects = response.data;
            } catch (error) {
                console.error('An error occurred while fetching the projects:', error);
            }
        },
        startTrackingTime() {
            const selectedProject = this.projects.find((p) => p.id === this.selectedProject);
            this.startTracking({
                selectedProject,
                isBillable: this.isBillable,
            });
        },
        async stopTrackingTime() {
            if (this.tracking.isTracking) {
                const startTime = new Date(this.tracking.startTime);
                const endTime = new Date();
                const durationInSeconds = Math.floor((endTime - startTime) / 1000);

                const formattedStartTime = startTime.toISOString().slice(0, 19).replace('T', ' ');
                const formattedEndTime = endTime.toISOString().slice(0, 19).replace('T', ' ');

                try {
                    await axios.post('/api/time-entries/add', {
                        projectId: this.tracking.selectedProject.id,
                        startTime: formattedStartTime,
                        endTime: formattedEndTime,
                        isBillable: this.tracking.isBillable,
                        duration: durationInSeconds,
                    });
                    this.stopTracking(); // Reset tracking state in Vuex store
                } catch (error) {
                    console.error('An error occurred while saving the time entry:', error);
                }
            }
        },
    },
    beforeDestroy() {
        this.stopTimer(); // Clear the timer when the component is destroyed
    },
};
</script>
