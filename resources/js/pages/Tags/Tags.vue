<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus } from 'lucide-vue-next';
import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    availableTags: {
        type: Array,
        default: () => [],
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const newTagName = ref('');
const isAddingTag = ref(false);
const isCreatingTag = ref(false);
const newTagInput = ref(null);

const selectedTags = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const toggleTag = (tag: string) => {
    const index = selectedTags.value.indexOf(tag);
    if (index > -1) {
        const updated = [...selectedTags.value];
        updated.splice(index, 1);
        selectedTags.value = updated;
    } else {
        selectedTags.value = [...selectedTags.value, tag];
    }
};

const showAddTagInput = async () => {
    isAddingTag.value = true;
    await nextTick();
    newTagInput.value?.focus();
};

const hideAddTagInput = () => {
    isAddingTag.value = false;
    newTagName.value = '';
};

const createNewTag = () => {
    const trimmedTag = newTagName.value.trim();

    if (!trimmedTag) {
        return;
    }

    // Check if tag already exists
    if (props.availableTags.includes(trimmedTag)) {
        // If it exists, just select it
        if (!selectedTags.value.includes(trimmedTag)) {
            toggleTag(trimmedTag);
        }
        hideAddTagInput();
        return;
    }

    // Create tag in database
    isCreatingTag.value = true;

    router.post('/tags', {
        name: trimmedTag,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            // Add to selected tags
            selectedTags.value = [...selectedTags.value, trimmedTag];

            toast('Tag created', {
                description: `Tag "${trimmedTag}" has been created and selected.`,
            });

            hideAddTagInput();
            isCreatingTag.value = false;
        },
        onError: (errors) => {
            toast.error('Failed to create tag', {
                description: errors.name || 'An error occurred while creating the tag.',
            });
            isCreatingTag.value = false;
        },
    });
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        createNewTag();
    } else if (event.key === 'Escape') {
        hideAddTagInput();
    }
};
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-wrap gap-2">
            <!-- Existing tags -->
            <Button
                v-for="tag in availableTags"
                :key="tag"
                type="button"
                :variant="selectedTags.includes(tag) ? 'default' : 'outline'"
                size="sm"
                @click="toggleTag(tag)"
                :disabled="disabled"
                class="text-xs"
            >
                {{ tag }}
            </Button>

            <!-- Add new tag input -->
            <div v-if="isAddingTag" class="flex items-center gap-1">
                <Input
                    ref="newTagInput"
                    v-model="newTagName"
                    type="text"
                    placeholder="New tag..."
                    class="h-8 w-32 text-xs"
                    :disabled="disabled || isCreatingTag"
                    @keydown="handleKeydown"
                    @blur="hideAddTagInput"
                />
            </div>

            <!-- Add tag button -->
            <Button
                v-else
                type="button"
                variant="ghost"
                size="sm"
                @click="showAddTagInput"
                :disabled="disabled"
                class="text-xs"
            >
                <Plus class="mr-1 h-3 w-3" />
                Add Tag
            </Button>
        </div>

        <p v-if="selectedTags.length > 0" class="text-xs text-muted-foreground">
            {{ selectedTags.length }} tag(s) selected
        </p>
    </div>
</template>
