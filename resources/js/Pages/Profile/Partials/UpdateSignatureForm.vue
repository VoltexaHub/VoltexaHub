<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const form = useForm({ signature: user.value.signature || '' });

const save = () => form.patch(route('profile.signature.update'), { preserveScroll: true });
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Signature</h2>
            <p class="mt-1 text-sm text-gray-600">
                A short markdown signature shown under each of your posts. Keep it brief — 2 or 3 lines works best.
            </p>
        </header>

        <form @submit.prevent="save" class="mt-6 space-y-4">
            <div>
                <textarea v-model="form.signature" rows="4" maxlength="1000"
                          placeholder="e.g. Building cool things. [Website](https://example.com)."
                          class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm"></textarea>
                <p class="text-xs text-gray-500 mt-1">{{ form.signature.length }} / 1000</p>
                <p v-if="form.errors.signature" class="text-sm text-red-600 mt-1">{{ form.errors.signature }}</p>
            </div>
            <div class="flex items-center gap-4">
                <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-700 disabled:opacity-50">
                    Save
                </button>
                <Transition leave-active-class="transition ease-in-out" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
