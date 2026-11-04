<script setup lang="ts">
import type { SiteCopy } from '@/types/site';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{ locale: string; action: string; copy: SiteCopy }>();
const form = useForm({
    name: '',
    email: '',
    company: '',
    service: '',
    message: '',
    website: '',
    source: 'contact',
    locale: props.locale,
});
const csrfToken = usePage().props.csrf_token as string;
const submit = () =>
    form.post(props.action, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
</script>

<template>
    <form
        class="lead-form"
        method="post"
        :action="action"
        @submit.prevent="submit"
    >
        <input type="hidden" name="_token" :value="csrfToken" />
        <input
            v-model="form.website"
            class="honeypot"
            name="website"
            tabindex="-1"
            autocomplete="off"
        />
        <label
            ><span>{{ copy.form.name }}</span
            ><input
                v-model="form.name"
                name="name"
                required
                autocomplete="name"
            /><small v-if="form.errors.name">{{
                form.errors.name
            }}</small></label
        >
        <label
            ><span>{{ copy.form.email }}</span
            ><input
                v-model="form.email"
                type="email"
                name="email"
                required
                autocomplete="email"
            /><small v-if="form.errors.email">{{
                form.errors.email
            }}</small></label
        >
        <label
            ><span>{{ copy.form.company }}</span
            ><input
                v-model="form.company"
                name="company"
                autocomplete="organization"
        /></label>
        <label
            ><span>{{ copy.form.service }}</span
            ><select v-model="form.service" name="service">
                <option value="">—</option>
                <option>Technical SEO</option>
                <option>AI Search Visibility</option>
                <option>AI Automation</option>
                <option>Content & Analytics</option>
            </select></label
        >
        <label class="form-wide"
            ><span>{{ copy.form.message }}</span
            ><textarea
                v-model="form.message"
                name="message"
                required
                rows="6"
            ></textarea
            ><small v-if="form.errors.message">{{
                form.errors.message
            }}</small></label
        >
        <div class="form-wide form-submit">
            <p>
                By sending this form, you agree that we may reply to your
                inquiry.
            </p>
            <button class="button" type="submit" :disabled="form.processing">
                {{ form.processing ? 'Sending…' : copy.form.submit }}
                <span>↗</span>
            </button>
        </div>
    </form>
</template>
