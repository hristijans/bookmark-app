<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ExternalLink, Pencil, Link2 } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Links',
        href: dashboard().url,
    },
];

const props = defineProps({
    links: Object,
})

const openLink = (url) => {
    window.open(url, '_blank', 'noopener,noreferrer');
};

const editLink = (link) => {
    // Implement your edit logic here
    // You can use Inertia.visit() to navigate to an edit page
    // or emit an event to open a modal
    console.log('Edit link:', link);
    // Example: router.visit(`/links/${link.id}/edit`);
};

const getDomain = (url) => {
    try {
        const urlObj = new URL(url);
        return urlObj.hostname.replace('www.', '');
    } catch {
        return url;
    }
};
</script>

<template>
    <Head title="Links" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <!-- Header -->
                <div class="border-b border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <h2 class="text-2xl font-semibold tracking-tight">Links</h2>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage your collection of links
                    </p>
                </div>

                <!-- Grid Container -->

                <div class="p-6">
                    <div v-if="links.data && links.total > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-6 xl:grid-cols-4">
                        <Card
                            v-for="link in links.data"
                            :key="link.id"
                            class="group overflow-hidden transition-all hover:shadow-lg"
                        >
                            <!-- Thumbnail -->
                            <div
                                class="relative aspect-video w-full cursor-pointer overflow-hidden bg-muted"
                                @click="openLink(link.url)"
                            >
                                <img
                                    v-if="link.thumbnail"
                                    :src="link.thumbnail"
                                    :alt="link.title"
                                    class="h-full w-full object-cover transition-transform group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary/10 to-primary/5"
                                >
                                    <ExternalLink class="h-12 w-12 text-muted-foreground/50" />
                                </div>

                                <!-- Overlay on hover -->
                                <div class="absolute inset-0 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100 flex items-center justify-center">
                                    <ExternalLink class="h-8 w-8 text-white" />
                                </div>
                            </div>

                            <!-- Content -->
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between gap-2">
                                    <CardTitle
                                        class="line-clamp-2 cursor-pointer text-base leading-tight hover:underline"
                                        @click="openLink(link.url)"
                                    >
                                        {{ link.title }}
                                    </CardTitle>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 shrink-0"
                                        @click="editLink(link)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                </div>
                            </CardHeader>

                            <CardContent class="pb-4">
                                <p class="line-clamp-2 text-sm text-muted-foreground">
                                    {{ link.description }}
                                </p>
                                <div class="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
                                    <Link2 class="h-3 w-3" />
                                    <span class="truncate">{{ getDomain(link.url) }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex min-h-[400px] flex-col items-center justify-center text-center">
                        <div class="rounded-full bg-muted p-6 mb-4">
                            <Link2 class="h-12 w-12 text-muted-foreground" />
                        </div>
                        <h3 class="text-lg font-semibold">No links yet</h3>
                        <p class="text-sm text-muted-foreground mt-2 max-w-sm">
                            Start building your link collection by adding your first link.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
