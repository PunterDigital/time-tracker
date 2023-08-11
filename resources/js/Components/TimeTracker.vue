<template>
    <div>
        <h1>Time Tracking</h1>
        <select v-model="selectedProject" @change="onProjectChange">
            <option value="" disabled>Select a project</option>
            <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.client }} - {{ project.name }}
            </option>
        </select>
        <button v-if="!isTracking" @click="startTracking">Start Tracking</button>
        <button v-if="isTracking" @click="stopTracking">Stop Tracking</button>
        <div v-if="isTracking">
            Tracking Time for: {{ currentProjectName }}
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            projects: [],
            selectedProject: '',
            isTracking: false,
            trackingStartTime: null,
        };
    },
    computed: {
        currentProjectName() {
            const project = this.projects.find((p) => p.id === this.selectedProject);
            return project ? `${project.client} - ${project.name}` : '';
        },
    },
    async created() {
        await this.fetchProjects();
    },
    methods: {
        async fetchProjects() {
            try {
                const response = await axios.get('/api/projects');
                this.projects = response.data;
            } catch (error) {
                console.error('An error occurred while fetching the projects:', error);
            }
        },
        onProjectChange() {
            this.stopTracking();
        },
        startTracking() {
            this.isTracking = true;
            this.trackingStartTime = new Date();
        },
        async stopTracking() {
            if (this.isTracking) {
                this.isTracking = false;
                const duration = new Date() - this.trackingStartTime;

                try {
                    await axios.post('/time-entries', {
                        projectId: this.selectedProject,
                        startTime: this.trackingStartTime.toISOString(),
                        endTime: new Date().toISOString(),
                        duration: Math.floor(duration / 1000),
                    });
                } catch (error) {
                    console.error('An error occurred while saving the time entry:', error);
                }
            }
        },
    },
};
</script>
