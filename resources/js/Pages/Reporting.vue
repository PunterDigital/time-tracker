<template>
    <AppLayout title="Reports">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reports
            </h2>
        </template>
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-semibold mb-4">Reporting</h1>

            <div class="mb-4 flex justify-between">
                <button @click="previousWeek" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Previous Week</button>
                <span>{{ startDate }} - {{ endDate }}</span>
                <button @click="nextWeek" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Next Week</button>
            </div>


            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">Week Breakdown</h2>
                <table class="min-w-full bg-white rounded-md shadow-sm">
                    <thead>
                    <tr class="border-b">
                        <th class="p-4 text-left">Day</th>
                        <th class="p-4 text-left">Total Hours</th>
                        <th class="p-4 text-left">% in Billable</th>
                        <th class="p-4 text-left">Chargeable</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="day in weekBreakdown" :key="day.name">
                        <td class="p-4">{{ day.name }}</td>
                        <td class="p-4">{{ formatTime(day.totalHours) }}</td>
                        <td class="p-4">{{ day.billablePercentage }}%</td>
                        <td class="p-4">${{ day.chargeable.toFixed(2) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-md shadow-sm p-4">
                <h2 class="text-xl font-semibold mb-2">Project Summary</h2>
                <table class="min-w-full">
                    <thead>
                    <tr class="border-b">
                        <th class="p-4 text-left">Client</th>
                        <th class="p-4 text-left">Project Name</th>
                        <th class="p-4 text-left">Total Time Tracked</th>
                        <th class="p-4 text-left">Total Billable Amount</th>
                        <th class="p-4 text-left">Billable Hours</th>
                        <th class="p-4 text-left">Non-Billable Hours</th>
                        <th class="p-4 text-left">Remaining Budget</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="project in projects" :key="project.id">
                        <td class="p-4">{{ project.client }}</td>
                        <td class="p-4">{{ project.name }}</td>
                        <td class="p-4">{{ formatTime(project.totalTime) }}</td>
                        <td class="p-4">${{ project.totalBillableAmount.toFixed(2) }}</td>
                        <td class="p-4">{{ formatTime(project.totalBillableTime) }}</td>
                        <td class="p-4">{{ formatTime(project.totalNonBillableTime) }}</td>
                        <td class="p-4">${{ project.remainingBudget ? project.remainingBudget.toFixed(2) : 'N/A' }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
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
            projects: [],
            weekBreakdown: [],
            startDate: this.getStartOfWeek(),
            endDate: this.getEndOfWeek(),
        };
    },
    async created() {
        await this.fetchWeekBreakdown();
        await this.fetchReports();
    },
    watch: {
        startDate: 'fetchWeekBreakdown',
        endDate: 'fetchWeekBreakdown',
    },
    methods: {
        previousWeek() {
            this.changeWeek(-7);
        },
        nextWeek() {
            this.changeWeek(7);
        },
        changeWeek(days) {
            const startDate = new Date(this.startDate);
            const endDate = new Date(this.endDate);

            startDate.setDate(startDate.getDate() + days);
            endDate.setDate(endDate.getDate() + days);

            this.startDate = startDate.toISOString().slice(0, 10);
            this.endDate = endDate.toISOString().slice(0, 10);

            // Call the method to update the week breakdown
            this.fetchWeekBreakdown();
        },
        getStartOfWeek() {
            const date = new Date();
            const day = date.getDay();
            const diff = date.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is Sunday
            return new Date(date.setDate(diff)).toISOString().slice(0, 10);
        },
        getEndOfWeek() {
            const date = new Date(this.getStartOfWeek());
            date.setDate(date.getDate() + 6);
            return date.toISOString().slice(0, 10);
        },
        async fetchWeekBreakdown() {
            try {
                const params = {
                    startDate: this.startDate,
                    endDate: this.endDate,
                };
                const response = await axios.get('/api/reports/week-breakdown', { params });
                this.weekBreakdown = response.data;
            } catch (error) {
                console.error('An error occurred while fetching the week breakdown:', error);
            }
        },
        async fetchReports() {
            try {
                const response = await axios.get('/api/reports');
                this.projects = response.data;
            } catch (error) {
                console.error('An error occurred while fetching the reports:', error);
            }
        },
        formatTime(seconds) {
            const hoursPart = Math.floor(seconds / 3600);
            const minutesPart = Math.floor((seconds % 3600) / 60);
            const secondsPart = Math.floor(seconds % 60);
            return `${hoursPart}h ${minutesPart}m ${secondsPart}s`;
        },
    }
};
</script>
