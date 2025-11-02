<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import FormLink from '@/pages/Links/FormLink.vue';

const props = defineProps({
    link: Object,
});

const modalOpened = ref(false);
const currentLink = ref(null);

const form = useForm({
    url: '',
    title: '',
    description: '',
    tags: [],
});

const open = (link) => {
    currentLink.value = link;
    form.url = link.url || '';
    form.title = link.title || '';
    form.description = link.description || '';
    form.tags = link.tags || [];
    modalOpened.value = true;
};

defineExpose({
    open,
});

const availableTags = [
    'JavaScript',
    'Vue.js',
    'React',
    'TypeScript',
    'CSS',
    'HTML',
    'Node.js',
    'Python',
    'Design',
    'Tutorial',
];

const toggleTag = (tag: string) => {
    const index = form.tags.indexOf(tag);
    if (index > -1) {
        form.tags.splice(index, 1);
    } else {
        form.tags.push(tag);
    }
};

const onSubmit = () => {
    if (!currentLink.value) return;

    form.put(`/links/${currentLink.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            modalOpened.value = false;
            toast('Link has been updated', {
                description: 'Your link was successfully updated.',
            });
            currentLink.value = null;
        },
        onError: () => {
            // Errors are shown inline
        },
    });
};
</script>

<template>
    <Sheet v-model:open="modalOpened">
        <SheetContent side="right" class="w-full overflow-y-auto sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Edit Link</SheetTitle>
                <SheetDescription>
                    Update the details of your link.
                </SheetDescription>
            </SheetHeader>

            <FormLink
                :form="form"
                submit-label="Submit"
                processing-label="Submitting..."
                @submit="onSubmit"
            />
        </SheetContent>
    </Sheet>
</template>
