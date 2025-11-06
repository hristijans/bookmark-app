<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import CreateLink from '@/pages/Links/CreateLink.vue';
import EditLink from '@/pages/Links/EditLink.vue';
import { dashboard } from '@/routes';
import { index as linksIndex } from '@/routes/links';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ExternalLink, Link2, Pencil, X } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Links',
        href: dashboard().url,
    },
];

const props = defineProps({
    links: Object,
    availableTags: Array,
    activeTag: { type: String, default: '' },
    activeTags: { type: Array as () => string[], default: () => [] },
    q: { type: String, default: '' },
    perPage: { type: Number, default: 10 },
    perPageOptions: { type: Array as () => number[], default: () => [10, 20, 50, 100] },
});

const activeTag = computed(() => props.activeTag || '');
const activeTags = computed<string[]>(() => props.activeTags ?? []);
const searchQuery = ref<string>(props.q || '');
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

// Watch for external changes to q (e.g., from URL)
watch(
    () => props.q,
    (val) => {
        if ((val || '') !== searchQuery.value) {
            searchQuery.value = val || '';
        }
    }
);

// Debounced watcher for searchQuery changes
watch(searchQuery, (newValue) => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        performSearch();
    }, 500); // 500ms delay - adjust as needed
});

function buildQuery(overrides: Record<string, any> = {}) {
    const query: Record<string, any> = {
        ...(activeTags.value.length ? { tags: activeTags.value } : {}),
        ...(searchQuery.value ? { q: searchQuery.value } : {}),
        per_page: props.perPage || 10,
        ...overrides,
    };
    return query;
}



function toggleTag(tag: string) {
    // Clear the debounce timer to prevent pending searches
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    const label = String(tag);
    const set = new Set(activeTags.value);

    if (set.has(label)) {
        set.delete(label);
    } else {
        set.add(label);
    }

    // Build query with or without tags
    const tags = Array.from(set);
    const query: Record<string, any> = {
        per_page: props.perPage || 10,
        page: 1,
        ...(searchQuery.value ? { q: searchQuery.value } : {}),
        ...(tags.length ? { tags } : {})
    };

    router.get(linksIndex.url(), query, { preserveState: true, preserveScroll: true });
}

function clearTags() {
    // Clear the debounce timer to prevent pending searches
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    const query: Record<string, any> = {
        per_page: props.perPage || 10,
        page: 1,
        ...(searchQuery.value ? { q: searchQuery.value } : {})
    };

    router.get(linksIndex.url(), query, { preserveState: true, preserveScroll: true });
}


function changePerPage(size: number) {
    const options: any = { mergeQuery: { ...buildQuery({ per_page: size, page: 1 }) } };
    router.get(linksIndex.url(options), {}, { preserveState: true, preserveScroll: true });
}

function performSearch() {
    const options: any = { mergeQuery: { ...buildQuery({ page: 1 }) } };
    router.get(linksIndex.url(options), {}, { preserveState: true, preserveScroll: true });
}

function submitSearch() {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    performSearch();
}

function clearSearch() {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    const query: Record<string, any> = {
        // ...(activeTags.value.length ? { tags: activeTags.value } : {}),
        per_page: props.perPage || 10,
        page: 1
    };

    // const options: any = {
    //     mergeQuery: query,
    //     replace: true  // Use replace to update URL without adding to history
    // };

    const options = {}

    router.get(linksIndex.url(options), {}, { preserveState: true, preserveScroll: true });
}

const editLinkRef = ref(null);

const openLink = (link) => {
    window.open(`/redirect/${link}`, '_blank', 'noopener,noreferrer');
};

const onEdit = (link) => {
    editLinkRef.value.open(link);
};

const getDomain = (url) => {
    try {
        const urlObj = new URL(url);
        return urlObj.hostname.replace('www.', '');
    } catch {
        return url;
    }
};

const tagLabel = (tag: any): string => {
    const name = tag?.name;
    if (name && typeof name === 'object') {
        const firstKey = Object.keys(name)[0];
        const value = (firstKey ? name[firstKey] : undefined);
        return typeof value === 'string' ? value : '';
    }
    return typeof name === 'string' ? name : '';
};

const thumbnailUrl = (thumbnail: string) => {
    return '/storage/' + thumbnail;
}
</script>

<template>
    <Head title="Links" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <!-- Header -->
                <div
                    class="border-b border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold tracking-tight">
                                Links
                            </h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Manage your collection of links
                            </p>
                        </div>
                        <div>
                            <CreateLink :available-tags="availableTags" />
                            <EditLink ref="editLinkRef" :available-tags="availableTags" />
                        </div>
                    </div>

                    <!-- Global Tags Filter (multi-select) + Search + Per Page selector -->
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                        <div v-if="availableTags && availableTags.length" class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="rounded-full border px-3 py-1 text-xs font-medium transition-colors hover:bg-muted"
                                :class="!activeTags.length ? 'bg-primary/10 text-primary border-primary/20' : 'border-sidebar-border/70 dark:border-sidebar-border'"
                                @click="clearTags()"
                            >
                                All
                            </button>
                            <button
                                v-for="t in availableTags"
                                :key="t"
                                type="button"
                                class="rounded-full border px-3 py-1 text-xs font-medium transition-colors hover:bg-muted"
                                :class="activeTags.includes(String(t)) ? 'bg-primary/10 text-primary border-primary/20' : 'border-sidebar-border/70 dark:border-sidebar-border'"
                                @click="toggleTag(String(t))"
                            >
                                {{ t }}
                            </button>
                        </div>

                        <!-- Per page dropdown in header -->
                        <div class="ml-auto flex items-center gap-2 text-xs">
                            <span class="text-muted-foreground">Show</span>
                            <select
                                class="rounded-md border bg-background px-2 py-1 text-xs"
                                :value="perPage"
                                @change="changePerPage(parseInt(($event.target as HTMLSelectElement).value))"
                            >
                                <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                            <span class="text-muted-foreground">per page</span>
                        </div>
                    </div>

                    <!-- Search input -->
                    <div class="flex w-full items-center gap-2 sm:w-auto my-4">
                        <Input
                            class="w-full sm:w-72"
                            placeholder="Search links (title, URL, description, tag names)"
                            v-model="searchQuery"
                            @keydown.enter.prevent="submitSearch"
                        />
                        <Button size="sm" @click="submitSearch">Search</Button>
                        <Button v-if="searchQuery" size="sm" variant="outline" @click="clearSearch">
                            <X class="mr-1 h-3.5 w-3.5" />
                            Clear
                        </Button>
                    </div>
                </div>

                <!-- Grid Container -->

                <div class="p-6">
                    <div
                        v-if="links.data && links.total > 0"
                        class="grid gap-6 sm:grid-cols-2 lg:grid-cols-6 xl:grid-cols-4"
                    >
                        <Card
                            v-for="link in links.data"
                            :key="link.id"
                            class="group overflow-hidden transition-all hover:shadow-lg"
                        >
                            <!-- Thumbnail -->
                            <div
                                class="relative aspect-video w-full cursor-pointer overflow-hidden bg-muted"
                                @click="openLink(link.id)"
                            >
                                <img
                                    v-if="link.thumbnail"
                                    :src="thumbnailUrl(link.thumbnail)"
                                    :alt="link.title"
                                    class="h-full w-full object-cover transition-transform group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary/10 to-primary/5"
                                >
                                    <ExternalLink
                                        class="h-12 w-12 text-muted-foreground/50"
                                    />
                                </div>

                                <!-- Overlay on hover -->
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <ExternalLink class="h-8 w-8 text-white" />
                                </div>
                            </div>

                            <!-- Content -->
                            <CardHeader class="pb-3">
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <div class="min-w-0 flex-1">
                                        <CardTitle
                                            class="line-clamp-2 cursor-pointer text-base leading-tight hover:underline"
                                            @click="openLink(link.id)"
                                        >
                                            {{ link.title }}
                                        </CardTitle>
                                        <!-- Tags under the title -->
                                        <div v-if="link.tags && link.tags.length" class="mt-2 flex flex-wrap gap-2">
                                            <Badge
                                                v-for="tag in link.tags"
                                                :key="tag.id ?? tag.slug ?? tagLabel(tag)"
                                                variant="secondary"
                                                class="px-2 py-0.5 text-xs cursor-pointer hover:opacity-80"
                                                @click="toggleTag(tagLabel(tag))"
                                            >
                                                {{ tagLabel(tag) }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 shrink-0"
                                        @click="onEdit(link)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                </div>
                            </CardHeader>

                            <CardContent class="pb-4">
                                <p
                                    class="line-clamp-2 text-sm text-muted-foreground"
                                >
                                    {{ link.description }}
                                </p>
                                <div
                                    class="mt-3 flex items-center gap-2 text-xs text-muted-foreground"
                                >
                                    <Link2 class="h-3 w-3" />
                                    <span class="truncate">{{
                                            getDomain(link.url)
                                        }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="flex min-h-[400px] flex-col items-center justify-center text-center"
                    >
                        <div class="mb-4 rounded-full bg-muted p-6">
                            <Link2 class="h-12 w-12 text-muted-foreground" />
                        </div>
                        <h3 class="text-lg font-semibold">No links yet</h3>
                        <p class="mt-2 max-w-sm text-sm text-muted-foreground">
                            Start building your link collection by adding your
                            first link.
                        </p>
                    </div>
                </div>

                <!-- Footer: Pagination controls -->
                <div v-if="links && links.total > 0" class="border-t border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border">
                    <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
                        <!-- Per page dropdown near pagination -->
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-muted-foreground">Show</span>
                            <select
                                class="rounded-md border bg-background px-2 py-1 text-xs"
                                :value="perPage"
                                @change="changePerPage(parseInt(($event.target as HTMLSelectElement).value))"
                            >
                                <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                            <span class="text-muted-foreground">per page</span>
                        </div>

                        <!-- Pagination links -->
                        <nav class="flex items-center gap-1" aria-label="Pagination">
                            <template v-if="Array.isArray(links.links) && links.links.length">
                                <button
                                    v-for="l in links.links"
                                    :key="l.label + String(l.url)"
                                    type="button"
                                    class="min-w-8 rounded border px-2 py-1 text-xs"
                                    :class="[
                                        l.active ? 'bg-primary/10 text-primary border-primary/20' : 'border-sidebar-border/70 dark:border-sidebar-border',
                                        !l.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-muted'
                                    ]"
                                    :disabled="!l.url"
                                    @click="l.url && router.get(l.url, {}, { preserveState: true, preserveScroll: true })"
                                    v-html="l.label"
                                />
                            </template>
                            <template v-else>
                                <button
                                    type="button"
                                    class="rounded border px-3 py-1 text-xs"
                                    :class="!links.prev_page_url ? 'opacity-50 cursor-not-allowed border-sidebar-border/70 dark:border-sidebar-border' : 'hover:bg-muted'"
                                    :disabled="!links.prev_page_url"
                                    @click="links.prev_page_url && router.get(links.prev_page_url, {}, { preserveState: true, preserveScroll: true })"
                                >
                                    Previous
                                </button>
                                <button
                                    type="button"
                                    class="rounded border px-3 py-1 text-xs"
                                    :class="!links.next_page_url ? 'opacity-50 cursor-not-allowed border-sidebar-border/70 dark:border-sidebar-border' : 'hover:bg-muted'"
                                    :disabled="!links.next_page_url"
                                    @click="links.next_page_url && router.get(links.next_page_url, {}, { preserveState: true, preserveScroll: true })"
                                >
                                    Next
                                </button>
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
