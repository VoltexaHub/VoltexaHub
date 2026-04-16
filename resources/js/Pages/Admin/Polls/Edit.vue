<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ poll: Object });

const toLocalInput = (iso) => {
    if (!iso) return '';
    const d = new Date(iso);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const form = useForm({
    question: props.poll.question,
    allow_multiple: !!props.poll.allow_multiple,
    closes_at: toLocalInput(props.poll.closes_at),
    options: props.poll.options.map((o) => ({ id: o.id, text: o.text, votes_count: o.votes_count })),
});

const addOption = () => {
    if (form.options.length >= 10) return;
    form.options.push({ id: null, text: '', votes_count: 0 });
};

const removeOption = (index) => {
    if (form.options.length <= 2) return;
    form.options.splice(index, 1);
};

const save = () => {
    form
        .transform((data) => ({
            ...data,
            closes_at: data.closes_at ? new Date(data.closes_at).toISOString() : null,
        }))
        .put(route('admin.polls.update', props.poll.id));
};
</script>

<template>
    <Head title="Admin · Edit Poll" />
    <AdminLayout>
        <header class="mb-8 pb-5 border-b" style="border-color:var(--border)">
            <p class="vx-meta mb-2">Editing poll</p>
            <h1 class="font-serif text-3xl font-semibold tracking-tight" style="font-family:'Fraunces',serif;color:var(--text)">
                {{ poll.question }}
            </h1>
            <p v-if="poll.thread" class="mt-2 text-sm" style="color:var(--text-muted)">
                on thread
                <Link :href="route('threads.show', [poll.thread.forum.slug, poll.thread.slug])"
                      class="hover:underline" :style="{ color: 'var(--accent)' }">
                    {{ poll.thread.title }}
                </Link>
            </p>
        </header>

        <form @submit.prevent="save" class="space-y-6 max-w-2xl">
            <div>
                <label class="vx-meta mb-2 block">Question</label>
                <input v-model="form.question" type="text" class="vx-input" required maxlength="250" />
                <p v-if="form.errors.question" class="text-sm text-red-600 mt-1">{{ form.errors.question }}</p>
            </div>

            <div>
                <label class="vx-meta mb-2 block">Closes at <span class="lowercase tracking-normal opacity-60 normal-case">(optional)</span></label>
                <input v-model="form.closes_at" type="datetime-local" class="vx-input font-mono" />
                <p v-if="form.errors.closes_at" class="text-sm text-red-600 mt-1">{{ form.errors.closes_at }}</p>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" v-model="form.allow_multiple" class="rounded"
                       style="border-color:var(--border);color:var(--accent)" />
                Allow voters to pick multiple options
            </label>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="vx-meta">Options <span class="lowercase tracking-normal opacity-60 normal-case">(2–10, drag to reorder by editing; order here is vote order)</span></label>
                    <button type="button" @click="addOption" :disabled="form.options.length >= 10"
                            class="text-xs hover:underline" :style="{ color: 'var(--accent)' }">
                        + Add option
                    </button>
                </div>
                <div class="space-y-2">
                    <div v-for="(opt, i) in form.options" :key="i"
                         class="flex items-center gap-2">
                        <span class="font-mono text-xs w-6 text-right" style="color:var(--text-subtle)">{{ i + 1 }}.</span>
                        <input v-model="opt.text" type="text" class="vx-input text-sm flex-1"
                               :placeholder="`Option ${i + 1}`" maxlength="200" required />
                        <span class="font-mono text-xs tabular-nums w-16 text-right" style="color:var(--text-muted)">
                            {{ opt.votes_count }} vote{{ opt.votes_count === 1 ? '' : 's' }}
                        </span>
                        <button type="button" @click="removeOption(i)" :disabled="form.options.length <= 2"
                                class="text-xs text-red-600 hover:underline disabled:opacity-30">
                            Remove
                        </button>
                    </div>
                </div>
                <p v-if="form.errors.options" class="text-sm text-red-600 mt-1">{{ form.errors.options }}</p>
                <p class="text-xs mt-2" style="color:var(--text-subtle)">
                    Removing an option deletes its votes. New options start with 0 votes.
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t" style="border-color:var(--border)">
                <Link :href="route('admin.polls.index')" class="vx-btn-secondary">Cancel</Link>
                <button type="submit" :disabled="form.processing" class="vx-btn-primary">Save</button>
            </div>
        </form>
    </AdminLayout>
</template>
