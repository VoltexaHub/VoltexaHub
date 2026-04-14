<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
    callbackUrls: Object,
});

const form = useForm({
    github_client_id: props.settings.oauth.github.client_id || '',
    github_client_secret: '',
    google_client_id: props.settings.oauth.google.client_id || '',
    google_client_secret: '',
});

const save = () => {
    form.put(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.github_client_secret = '';
            form.google_client_secret = '';
        },
    });
};

const clearSecret = (provider) => {
    if (confirm(`Clear ${provider} client secret?`)) {
        router.delete(route('admin.settings.oauth.clear-secret'), {
            data: { provider },
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Settings" />
    <AdminLayout>
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Settings</h1>

        <form @submit.prevent="save" class="space-y-8 max-w-3xl">
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <header class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-800">OAuth providers</h2>
                    <p class="text-sm text-gray-500">Leave a provider blank to hide its sign-in button. Secrets are stored encrypted.</p>
                </header>

                <div class="p-4 border-b border-gray-100 space-y-3">
                    <h3 class="font-medium text-gray-800">GitHub</h3>
                    <p class="text-xs text-gray-500">
                        Callback URL: <code class="bg-gray-100 px-1 rounded">{{ callbackUrls.github }}</code>
                        · Register at
                        <a href="https://github.com/settings/developers" target="_blank" class="text-indigo-600 hover:underline">github.com/settings/developers</a>
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                        <input v-model="form.github_client_id" type="text" class="w-full rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Client Secret
                            <span v-if="settings.oauth.github.has_secret" class="text-xs text-green-600 ml-2">✓ saved</span>
                        </label>
                        <input v-model="form.github_client_secret" type="password" autocomplete="off"
                               :placeholder="settings.oauth.github.has_secret ? 'Leave blank to keep current' : 'Paste secret here'"
                               class="w-full rounded border-gray-300" />
                        <button v-if="settings.oauth.github.has_secret" type="button"
                                @click="clearSecret('github')"
                                class="mt-2 text-xs text-red-600 hover:underline">Clear saved secret</button>
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    <h3 class="font-medium text-gray-800">Google</h3>
                    <p class="text-xs text-gray-500">
                        Callback URL: <code class="bg-gray-100 px-1 rounded">{{ callbackUrls.google }}</code>
                        · Register at
                        <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-indigo-600 hover:underline">console.cloud.google.com/apis/credentials</a>
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                        <input v-model="form.google_client_id" type="text" class="w-full rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Client Secret
                            <span v-if="settings.oauth.google.has_secret" class="text-xs text-green-600 ml-2">✓ saved</span>
                        </label>
                        <input v-model="form.google_client_secret" type="password" autocomplete="off"
                               :placeholder="settings.oauth.google.has_secret ? 'Leave blank to keep current' : 'Paste secret here'"
                               class="w-full rounded border-gray-300" />
                        <button v-if="settings.oauth.google.has_secret" type="button"
                                @click="clearSecret('google')"
                                class="mt-2 text-xs text-red-600 hover:underline">Clear saved secret</button>
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 disabled:opacity-50">
                    Save Settings
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
