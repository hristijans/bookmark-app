import { defineStore } from 'pinia';

export const useRecordModal = defineStore('createLink', {
    state: () => ({
        isOpen: false as boolean,
        // If you need to prefill or pass context (e.g. parent id), add it here
        context: null as null | { [key: string]: any },
    }),
    actions: {
        open(context: any = null) {
            this.context = context;
            this.isOpen = true;
        },
        close() {
            this.isOpen = false;
            this.context = null;
        },
    },
});
