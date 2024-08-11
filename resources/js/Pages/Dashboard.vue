<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="max-w-7xl mx-auto p-4">
            <!-- Week View Section -->
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-4">Week View</h2>
                <div class="grid grid-cols-7 gap-4">
                    <div v-for="(day, index) in weekView" :key="index"
                         class="p-4 rounded-md shadow-sm cursor-pointer hover:bg-gray-100"
                         :class="{'bg-white': index === currentDayIndex, 'bg-gray-50': index !== currentDayIndex}"
                         @click="loadDayProjects(index)">
                        <h3 class="font-semibold">{{ day.name }}</h3>
                        <p>{{ formatTime(day.totalTime) }}</p>
                    </div>
                </div>
            </div>

            <!-- Modal or separate component to display selected day's projects -->
            <div v-if="selectedDay" class="bg-white p-4 rounded-md shadow-sm mt-4">
                <h3 class="font-semibold">{{ selectedDay.name }}</h3>
                <ul>
                    <li v-for="project in selectedDay.projects" :key="project.id">
                        {{ project.name }}: {{ formatTime(project.time) }} | {{ project.memo }}
                    </li>
                </ul>
            </div>

            <h1 class="mt-6 text-2xl font-semibold mb-4">Time Tracking</h1>
            <div v-if="tracking.isTracking" class="bg-blue-100 p-4 rounded-md shadow-sm">
                <p class="mb-2">
                    <span class="font-semibold">Tracking project:</span> {{ currentProjectName }}<br />
                    <span class="font-semibold">Time tracked:</span> {{ currentTimeTracked }}
                </p>

                <div class="mb-2">
                    <label for="memo" class="block text-sm font-medium leading-6 text-gray-900">Add a memo</label>
                    <div class="mt-2">
                        <textarea v-model="form.memo" rows="4" name="memo" id="memo" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                </div>

                <button @click="stopTrackingTime" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                    Stop Tracking
                </button>
            </div>
            <div v-else class="bg-gray-100 p-4 rounded-md shadow-sm mt-4">
                <div class="mb-3">
                    <select v-model="selectedProject" @change="onProjectChange()" class="border rounded-md p-2 w-full">
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

                <div class="mb-2">
                    <label for="memo" class="block text-sm font-medium leading-6 text-gray-900">Add a memo</label>
                    <div class="mt-2">
                        <textarea v-model="form.memo" rows="4" name="memo" id="memo" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                </div>

                <button v-if="!isTracking && !selectedProject" @click="startTrackingTime" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:cursor-not-allowed" disabled>
                    Select a project to track time on
                </button>

                <button v-else @click="startTrackingTime" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                    Start Tracking
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useStore } from 'vuex';
import {router, useForm, usePage} from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";

const store = useStore();
const page = usePage();

// State
const selectedProject = ref('');
const isBillable = ref(true);
const billingRate = ref(0.00);
const currentTimeTracked = ref('0h 0m 0s');
const weekDays = ref(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']);
const selectedDay = ref('');
const currentDayIndex = ref(new Date().getDay() - 1);
const dayTotalHours = ref({});
const weekView = ref(page.props.weekView.original);
let timer = null;

// Inertia form
const form = useForm({
    projectId: '',
    startTime: '',
    endTime: '',
    isBillable: true,
    duration: 0,
    billingRate: 0,
    memo: '',
});

// Computed properties
const tracking = computed(() => store.state.tracking);
const currentProjectName = computed(() =>
    tracking.value.selectedProject
        ? `${tracking.value.selectedProject.client} - ${tracking.value.selectedProject.name}`
        : ''
);

// Access projects from Inertia shared data
const projects = computed(() => page.props.projects);

// Methods
const onProjectChange = () => {
    const project = projects.value.find(p => p.id === selectedProject.value);
    billingRate.value = project.billing_rate;
};

const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    return `${hours}h ${minutes}m ${remainingSeconds}s`;
};

const loadDayProjects = (index) => {
    selectedDay.value = weekView.value[index];
};

const startTimer = () => {
    timer = setInterval(() => {
        if (tracking.value.isTracking) {
            const startTime = new Date(tracking.value.startTime);
            const currentTime = new Date();
            const durationInSeconds = (currentTime - startTime) / 1000;
            const hours = Math.floor(durationInSeconds / 3600);
            const minutes = Math.floor((durationInSeconds % 3600) / 60);
            const seconds = Math.floor(durationInSeconds % 60);
            currentTimeTracked.value = `${hours}h ${minutes}m ${seconds}s`;
        }
    }, 1000);
};

const stopTimer = () => {
    clearInterval(timer);
    currentTimeTracked.value = '0h 0m 0s';
};

const startTrackingTime = () => {
    const selectedProjectObj = projects.value.find((p) => p.id === selectedProject.value);
    store.commit('startTracking', {
        selectedProject: selectedProjectObj,
        isBillable: isBillable.value,
    });
};

const stopTrackingTime = () => {
    if (tracking.value.isTracking) {
        const startTime = new Date(tracking.value.startTime);
        const endTime = new Date();
        const durationInSeconds = Math.floor((endTime - startTime) / 1000);

        const formattedStartTime = startTime.toISOString().slice(0, 19).replace('T', ' ');
        const formattedEndTime = endTime.toISOString().slice(0, 19).replace('T', ' ');

        form.projectId = tracking.value.selectedProject.id;
        form.startTime = formattedStartTime;
        form.endTime = formattedEndTime;
        form.isBillable = tracking.value.isBillable;
        form.duration = durationInSeconds;
        form.billingRate = billingRate.value;

        // todo: test if others can view time entries.

        form.post(route('time-entries.store'), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (response) => {
                store.commit('stopTracking'); // Reset tracking state in Vuex store

                // Optionally, refetch the entire page data to ensure consistency
                router.reload({ only: ['weekView'] });
            },
            onError: (errors) => {
                console.error('An error occurred while saving the time entry:', errors);
            },
        });
    }
};

// Watchers
watch(() => tracking.value.isTracking, (isTracking) => {
    if (isTracking) {
        startTimer();
    } else {
        stopTimer();
    }
}, { immediate: true });

// Lifecycle hooks
onMounted(() => {
    store.commit('loadTrackingFromStorage');
    loadDayProjects(currentDayIndex.value);
});

onBeforeUnmount(() => {
    stopTimer(); // Clear the timer when the component is unmounted
});
</script>
