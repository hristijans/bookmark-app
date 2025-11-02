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

            <form class="mt-8 space-y-6 px-4" @submit.prevent="onSubmit">
                <FormField v-slot="{ componentField }" name="url">
                    <FormItem>
                        <FormLabel>URL</FormLabel>
                        <FormControl>
                            <Input
                                type="url"
                                v-model="form.url"
                                placeholder="https://example.com"
                                :disabled="form.processing"
                            />
                        </FormControl>
                        <p
                            v-if="form.errors.url"
                            class="text-sm font-medium text-destructive"
                        >
                            {{ form.errors.url }}
                        </p>
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="title">
                    <FormItem>
                        <FormLabel>Title</FormLabel>
                        <FormControl>
                            <Input
                                type="text"
                                v-model="form.title"
                                placeholder="Enter link title"
                                :disabled="form.processing"
                            />
                        </FormControl>
                        <p
                            v-if="form.errors.title"
                            class="text-sm font-medium text-destructive"
                        >
                            {{ form.errors.title }}
                        </p>
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="description">
                    <FormItem>
                        <FormLabel>Description</FormLabel>
                        <FormControl>
                            <Textarea
                                v-model="form.description"
                                placeholder="Enter a description for this link"
                                :disabled="form.processing"
                                rows="4"
                                class="resize-none"
                            />
                        </FormControl>
                        <p
                            v-if="form.errors.description"
                            class="text-sm font-medium text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="tags">
                    <FormItem>
                        <FormLabel>Tags</FormLabel>
                        <FormControl>
                            <div class="space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        v-for="tag in availableTags"
                                        :key="tag"
                                        type="button"
                                        :variant="
                                            form.tags.includes(tag)
                                                ? 'default'
                                                : 'outline'
                                        "
                                        size="sm"
                                        @click="toggleTag(tag)"
                                        :disabled="form.processing"
                                        class="text-xs"
                                    >
                                        {{ tag }}
                                    </Button>
                                </div>
                                <p
                                    v-if="form.tags.length > 0"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ form.tags.length }} tag(s) selected
                                </p>
                            </div>
                        </FormControl>
                        <p
                            v-if="form.errors.tags"
                            class="text-sm font-medium text-destructive"
                        >
                            {{ form.errors.tags }}
                        </p>
                    </FormItem>
                </FormField>

                <div class="flex gap-3 pt-4">
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1"
                    >
                        {{ form.processing ? 'Updating...' : 'Update' }}
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        @click="modalOpened = false"
                        :disabled="form.processing"
                    >
                        Cancel
                    </Button>
                </div>
            </form>
        </SheetContent>
    </Sheet>
</template>
