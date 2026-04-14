<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    reports: Object,
    counts: Object,
    filter: String,
});

const tabs = [
    { key: 'pending', label: 'Pending' },
    { key: 'resolved', label: 'Resolved' },
    { key: 'dismissed', label: 'Dismissed' },
];

const dismiss = (r) => {
    if (confirm('Dismiss this report?')) router.post(route('admin.reports.dismiss', r.id), {}, { preserveScroll: true });
};
const resolveDelete = (r) => {
    if (confirm('Delete the reported post and resolve all reports on it?')) {
        router.post(route('admin.reports.resolve-delete', r.id), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Reports" />
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Reports</h1>
            <nav class="flex gap-1 text-sm">
                <Link v-for="t in tabs" :key="t.key"
                      :href="route('admin.reports.index', { status: t.key })"
                      :class="['px-3 py-1.5 rounded border',
                        filter === t.key ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50']">
                    {{ t.label }} ({{ counts[t.key] }})
                </Link>
            </nav>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <ul class="divide-y divide-gray-100">
                <li v-for="r in reports.data" :key="r.id" class="p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm">
                                <span class="font-medium text-gray-900">{{ r.reporter?.name }}</span>
                                <span class="text-gray-500"> reported </span>
                                <Link v-if="r.post?.author"
                                      :href="route('users.show', r.post.author)"
                                      class="font-medium text-gray-900 hover:text-indigo-600">{{ r.post.author.name }}</Link>
                                <span v-else class="text-gray-500">[deleted user]</span>
                                <span class="text-gray-500"> for </span>
                                <span class="inline-block px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-800">{{ r.reason }}</span>
                                <span class="text-gray-400 text-xs ml-2">{{ new Date(r.created_at).toLocaleString() }}</span>
                            </div>
                            <p v-if="r.note" class="text-sm text-gray-600 mt-1 italic">"{{ r.note }}"</p>

                            <div v-if="r.post" class="mt-3 border-l-4 border-gray-200 pl-3">
                                <div class="text-xs text-gray-500 mb-1">
                                    <Link :href="route('threads.show', [r.post.thread.forum.slug, r.post.thread.slug]) + '#post-' + r.post.id"
                                          class="text-indigo-600 hover:underline">
                                        {{ r.post.thread.title }}
                                    </Link>
                                    <span class="ml-1">in {{ r.post.thread.forum.name }}</span>
                                </div>
                                <p class="text-sm text-gray-700 line-clamp-3 whitespace-pre-wrap">{{ r.post.body }}</p>
                            </div>
                            <p v-else class="text-sm text-gray-500 italic mt-2">[post deleted]</p>

                            <p v-if="r.resolver" class="text-xs text-gray-400 mt-2">
                                {{ r.status }} by {{ r.resolver.name }} · {{ new Date(r.resolved_at).toLocaleString() }}
                            </p>
                        </div>
                        <div v-if="filter === 'pending' && r.post" class="flex flex-col gap-1 shrink-0">
                            <button @click="resolveDelete(r)" class="text-xs px-3 py-1.5 rounded border border-red-200 text-red-700 hover:bg-red-50">
                                Delete post
                            </button>
                            <button @click="dismiss(r)" class="text-xs px-3 py-1.5 rounded border border-gray-200 text-gray-700 hover:bg-gray-50">
                                Dismiss
                            </button>
                        </div>
                    </div>
                </li>
                <li v-if="reports.data.length === 0" class="p-8 text-center text-gray-500">No {{ filter }} reports.</li>
            </ul>
        </div>

        <div v-if="reports.links" class="mt-4 flex flex-wrap gap-1">
            <Link v-for="(link, i) in reports.links" :key="i" :href="link.url || '#'" v-html="link.label"
                :class="['px-3 py-1 text-sm border rounded', link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50', !link.url && 'opacity-50 pointer-events-none']" />
        </div>
    </AdminLayout>
</template>
