<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { toast } from 'vue-sonner';

import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const modalOpened = ref(false);

const form = useForm({
    url: '',
    title: '',
    description: '',
});

const onSubmit = () => {
    form.post('/links', {
        preserveScroll: true,
        onSuccess: () => {
            // Optionally show a flash message via shared props
            modalOpened.value = false;
            toast('Event has been created', {
                description: 'Sunday, December 03, 2023 at 9:00 AM',
                action: {
                    label: 'Undo',
                    onClick: () => console.log('Undo'),
                },
            });
            form.reset();
        },
        // Inertia will populate form.errors on 422 validation failures
        onError: () => {
            // keep modal open; errors are shown inline
        },
        onFinish: () => {
            // form.processing becomes false automatically
            modalOpened.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Sheet v-model:open="modalOpened">
        <SheetTrigger as-child>
            <Button variant="outline"> Open from Right </Button>
        </SheetTrigger>
        <SheetContent side="right">
            <SheetHeader>
                <SheetTitle>Right-Side Sheet</SheetTitle>
                <SheetDescription>
                    This content slides in from the right.
                </SheetDescription>
            </SheetHeader>
            <!-- Your sheet content goes here -->
            <div>
                <form class="w-2/3 space-y-6" @submit.prevent="onSubmit">
                    <Input type="text" v-model="form.url" placeholder="URL" />
                    <Input type="text" v-model="form.title" placeholder="URL" />
                    <Input
                        type="text"
                        v-model="form.description"
                        placeholder="Text"
                    />
                    <Button type="submit"> Submit </Button>
                </form>
            </div>
        </SheetContent>
    </Sheet>
</template>
