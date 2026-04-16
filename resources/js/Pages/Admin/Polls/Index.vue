<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ polls: Object, filters: Object });

const q = ref(props.filters.q || '');
let t;
watch(q, (val) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('admin.polls.index'), { q: val }, { preserveState: true, replace: true }), 300);
});

const destroy = (poll) => {
    if (confirm(`Delete poll "${poll.question}"? Votes will be lost. The thread stays.`)) {
        router.delete(route('admin.polls.destroy', poll.id), { preserveScroll: true });
    }
};

const fmt = (d) => d ? new Date(d).toLocaleDateString() : '—';
</script>

<template>
    <Head title="Admin · Polls" />
    <AdminLayout>
        <header class="flex items-end justify-between mb-8 pb-5 border-b" style="border-color:var(--border)">
            <div>
                <p class="vx-meta mb-2">Moderation</p>
                <h1 class="font-serif text-4xl font-semibold tracking-tight" style="font-family:'Fraunces',serif;color:var(--text)">Polls</h1>
            </div>
            <input v-model="q" type="search" placeholder="Search questions…" class="vx-input text-sm max-w-xs" />
        </header>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left" style="color:var(--text-subtle)">
                    <th class="py-2 vx-meta">Question</th>
                    <th class="py-2 vx-meta">Thread</th>
                    <th class="py-2 vx-meta w-20 text-right">Options</th>
                    <th class="py-2 vx-meta w-20 text-right">Votes</th>
                    <th class="py-2 vx-meta w-28">Closes</th>
                    <th class="py-2 vx-meta w-40 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="poll in polls.data" :key="poll.id" class="border-t" :style="{ borderColor: 'var(--border)' }">
                    <td class="py-4 font-medium" style="color:var(--text)">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span v-if="poll.allow_multiple" class="vx-chip">Multi-select</span>
                            <span>{{ poll.question }}</span>
                        </div>
                    </td>
                    <td class="py-4" style="color:var(--text-muted)">
                        <Link v-if="poll.thread" :href="route('threads.show', [poll.thread.forum.slug, poll.thread.slug])"
                              class="hover:underline" :style="{ color: 'var(--accent)' }">
                            {{ poll.thread.title }}
                        </Link>
                        <span v-else>—</span>
                    </td>
                    <td class="py-4 text-right tabular-nums" style="color:var(--text-muted)">{{ poll.options_count }}</td>
                    <td class="py-4 text-right tabular-nums" style="color:var(--text-muted)">{{ poll.votes_count }}</td>
                    <td class="py-4 font-mono text-xs" style="color:var(--text-subtle)">{{ fmt(poll.closes_at) }}</td>
                    <td class="py-4 text-right space-x-3 text-xs">
                        <Link :href="route('admin.polls.edit', poll.id)" class="hover:underline" :style="{ color: 'var(--accent)' }">Edit</Link>
                        <button @click="destroy(poll)" class="hover:underline text-red-600">Delete</button>
                    </td>
                </tr>
                <tr v-if="polls.data.length === 0">
                    <td colspan="6" class="py-16 text-center italic" style="color:var(--text-muted)">No polls found.</td>
                </tr>
            </tbody>
        </table>

        <div v-if="polls.links" class="mt-6 flex flex-wrap gap-1">
            <Link v-for="(link, i) in polls.links" :key="i" :href="link.url || '#'" v-html="link.label"
                class="px-3 py-1 text-sm border rounded-md font-mono"
                :class="[link.active ? 'text-white' : '', !link.url && 'opacity-40 pointer-events-none']"
                :style="{
                    background: link.active ? 'var(--accent)' : 'var(--surface)',
                    borderColor: link.active ? 'var(--accent)' : 'var(--border)',
                    color: link.active ? '#fff' : 'var(--text-muted)',
                }" />
        </div>
    </AdminLayout>
</template>
