<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import Tags from '@/pages/Tags/Tags.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    availableTags: {
        type: Array,
        default: () => [],
    },
    submitLabel: {
        type: String,
        default: 'Submit',
    },
    processingLabel: {
        type: String,
        default: 'Submitting...',
    },
});

const emit = defineEmits(['submit']);

const onSubmit = () => {
    emit('submit');
};
</script>

<template>
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
                    <Tags
                        v-model="form.tags"
                        :available-tags="availableTags"
                        :disabled="form.processing"
                    />
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
                {{ form.processing ? processingLabel : submitLabel }}
            </Button>
        </div>
    </form>
</template>
