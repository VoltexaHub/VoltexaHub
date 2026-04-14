<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ thread: Object, forums: Array });

const form = useForm({
    title: props.thread.title,
    slug: props.thread.slug,
    forum_id: props.thread.forum_id,
    is_pinned: !!props.thread.is_pinned,
    is_locked: !!props.thread.is_locked,
});

const save = () => form.put(route('admin.threads.update', props.thread.id));
</script>

<template>
    <Head title="Admin · Edit Thread" />
    <AdminLayout>
        <header class="mb-8 pb-5 border-b" style="border-color:var(--border)">
            <p class="vx-meta mb-2">Editing thread</p>
            <h1 class="font-serif text-3xl font-semibold tracking-tight" style="font-family:'Fraunces',serif;color:var(--text)">
                {{ thread.title }}
            </h1>
        </header>

        <form @submit.prevent="save" class="space-y-6 max-w-2xl">
            <div>
                <label class="vx-meta mb-2 block">Title</label>
                <input v-model="form.title" type="text" class="vx-input" required maxlength="200" />
                <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</p>
            </div>
            <div>
                <label class="vx-meta mb-2 block">Slug <span class="lowercase tracking-normal opacity-60 normal-case">(unique within forum)</span></label>
                <input v-model="form.slug" type="text" class="vx-input font-mono" maxlength="220" />
                <p v-if="form.errors.slug" class="text-sm text-red-600 mt-1">{{ form.errors.slug }}</p>
            </div>
            <div>
                <label class="vx-meta mb-2 block">Forum</label>
                <select v-model="form.forum_id" class="vx-input">
                    <option v-for="f in forums" :key="f.id" :value="f.id">{{ f.name }}</option>
                </select>
            </div>
            <div class="flex items-center gap-6 text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.is_pinned" class="rounded" style="border-color:var(--border);color:var(--accent)" />
                    Pinned
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.is_locked" class="rounded" style="border-color:var(--border);color:var(--accent)" />
                    Locked
                </label>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t" style="border-color:var(--border)">
                <Link :href="route('admin.threads.index')" class="vx-btn-secondary">Cancel</Link>
                <button type="submit" :disabled="form.processing" class="vx-btn-primary">Save</button>
            </div>
        </form>
    </AdminLayout>
</template>
