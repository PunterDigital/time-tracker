import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate';

const initialState = {
    tracking: {
        isTracking: false,
        startTime: null,
        selectedProject: null,
        isBillable: false,
        // ... other initial state values ...
    },
    // ... other state properties ...
};

export default new Vuex.Store({
    state: {
        tracking: {
            isTracking: false,
            startTime: null,
            selectedProject: null,
            isBillable: true,
        },
    },
    plugins: [createPersistedState({
        paths: ['tracking'],
    })],
    mutations: {
        resetState(state) {
            Object.assign(state, initialState);
        },
        startTracking(state, payload) {
            state.tracking.isTracking = true;
            state.tracking.startTime = new Date().toISOString();
            state.tracking.selectedProject = payload.selectedProject;
            state.tracking.isBillable = payload.isBillable;
        },
        stopTracking(state) {
            state.tracking.isTracking = false;
            state.tracking.startTime = null;
            state.tracking.selectedProject = null;
            state.tracking.isBillable = true;
            localStorage.removeItem('tracking');
        },
        loadTrackingFromStorage(state) {
            const trackingData = localStorage.getItem('tracking');
            if (trackingData) {
                state.tracking = JSON.parse(trackingData);
            }
        },
    },
});
