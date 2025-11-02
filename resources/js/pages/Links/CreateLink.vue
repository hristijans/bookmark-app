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
    SheetTrigger,
} from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import FormLink from '@/pages/Links/FormLink.vue';

const modalOpened = ref(false);

const form = useForm({
    url: '',
    title: '',
    description: '',
    tags: [],
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
    form.post('/links', {
        preserveScroll: true,
        onSuccess: () => {
            modalOpened.value = false;
            toast('Link has been created', {
                description: 'Your link was successfully added.',
                action: {
                    label: 'Undo',
                    onClick: () => console.log('Undo'),
                },
            });
            form.reset();
        },
        onError: () => {
            // Errors are shown inline
        },
    });
};
</script>

<template>
    <Sheet v-model:open="modalOpened">
        <SheetTrigger as-child>
            <Button variant="outline"> Add New Link </Button>
        </SheetTrigger>
        <SheetContent side="right" class="w-full overflow-y-auto sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Add New Link</SheetTitle>
                <SheetDescription>
                    Fill in the details to add a new link to your collection.
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
