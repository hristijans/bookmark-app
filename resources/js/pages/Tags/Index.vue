<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Pencil, Trash2, Tag as TagIcon, Plus } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps({
  tags: {
    type: Array as () => Array<{ id: number; name: any; slug?: string; links_count?: number }>,
    default: () => []
  }
})

const createForm = useForm({
  name: ''
})

const editingId = ref<number | null>(null)
const editForm = useForm({
  name: ''
})

const onEdit = (tag: any) => {
  editingId.value = tag.id
  // Handle translatable name (Spatie Tags stores names per locale)
  editForm.name = (tag?.name?.en ?? tag?.name ?? '').toString()
}

const cancelEdit = () => {
  editingId.value = null
  editForm.reset()
}

const submitCreate = () => {
  if (!createForm.name?.trim()) return
  createForm.post('/tags', {
    preserveScroll: true,
    onSuccess: () => {
      createForm.reset()
    }
  })
}

const submitEdit = (tagId: number) => {
  if (!editForm.name?.trim()) return
  editForm.put(`/tags/${tagId}`, {
    preserveScroll: true,
    onSuccess: () => {
      cancelEdit()
    }
  })
}

const destroy = (tagId: number) => {
  router.delete(`/tags/${tagId}`, { preserveScroll: true })
}
</script>

<template>
  <Head title="Tags" />
  <AppLayout :breadcrumbs="[{ title: 'Tags' }]">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
        <div class="border-b border-sidebar-border/70 p-6 dark:border-sidebar-border">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-semibold tracking-tight">Tags</h2>
              <p class="mt-1 text-sm text-muted-foreground">List, create, edit and delete tags</p>
            </div>
          </div>
        </div>

        <div class="p-6 space-y-6">
          <!-- Create -->
          <Card>
            <CardHeader class="pb-3">
              <CardTitle class="text-base">Create Tag</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex items-center gap-2">
                <Input
                  v-model="createForm.name"
                  placeholder="Tag name"
                  :disabled="createForm.processing"
                  class="max-w-xs"
                  @keydown.enter.prevent="submitCreate"
                />
                <Button :disabled="createForm.processing" @click="submitCreate">
                  <Plus class="mr-2 h-4 w-4" />
                  Create
                </Button>
              </div>
              <p v-if="createForm.errors.name" class="mt-2 text-sm font-medium text-destructive">
                {{ createForm.errors.name }}
              </p>
            </CardContent>
          </Card>

          <!-- List -->
          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card v-for="tag in props.tags" :key="tag.id" class="overflow-hidden">
              <CardHeader class="pb-2">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <TagIcon class="h-4 w-4 text-muted-foreground" />
                    <CardTitle v-if="editingId !== tag.id" class="text-base">
                      {{ (tag?.name?.en ?? tag?.name ?? '').toString() }}
                    </CardTitle>
                    <Input
                      v-else
                      v-model="editForm.name"
                      placeholder="Tag name"
                      class="max-w-xs"
                      :disabled="editForm.processing"
                      @keydown.enter.prevent="submitEdit(tag.id)"
                    />
                  </div>
                  <div class="flex items-center gap-1">
                    <template v-if="editingId === tag.id">
                      <Button size="sm" variant="outline" :disabled="editForm.processing" @click="cancelEdit">
                        Cancel
                      </Button>
                      <Button size="sm" :disabled="editForm.processing" @click="submitEdit(tag.id)">
                        Save
                      </Button>
                    </template>
                    <template v-else>
                      <Button size="icon" variant="ghost" @click="onEdit(tag)">
                        <Pencil class="h-4 w-4" />
                      </Button>
                      <Button size="icon" variant="ghost" @click="destroy(tag.id)">
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </template>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <p class="text-xs text-muted-foreground">
                  {{ (tag.links_count ?? 0) }} {{ (tag.links_count ?? 0) === 1 ? 'Link' : 'Links' }} with this tag
                </p>
              </CardContent>
            </Card>
          </div>

          <div v-if="props.tags.length === 0" class="text-sm text-muted-foreground">
            No tags yet. Create your first tag above.
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
