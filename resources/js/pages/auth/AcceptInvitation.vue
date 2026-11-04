<script setup lang="ts">
import LogoMark from '@/components/brand/LogoMark.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    token: string;
    email: string;
    organization: string;
}>();
const form = useForm({ name: '', password: '', password_confirmation: '' });
const submit = () => form.post(`/invitation/${props.token}`);
</script>

<template>
    <Head title="Accept invitation" />
    <main class="invite-page">
        <section>
            <LogoMark />
            <p class="eyebrow"><span></span>PRIVATE CLIENT PORTAL</p>
            <h1>Welcome to<br />SEO Impact.</h1>
            <p>
                You were invited to join <strong>{{ organization }}</strong
                >. Create your private account to see projects, tasks, billing
                and files.
            </p>
        </section>
        <form @submit.prevent="submit">
            <h2>Activate your account</h2>
            <label>Email<input :value="email" disabled /></label
            ><label
                >Your name<input
                    v-model="form.name"
                    required
                    autocomplete="name"
                /><small>{{ form.errors.name }}</small></label
            ><label
                >Password<input
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                /><small>{{ form.errors.password }}</small></label
            ><label
                >Confirm password<input
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password" /></label
            ><button class="button" type="submit" :disabled="form.processing">
                {{ form.processing ? 'Activating…' : 'Activate account' }} ↗
            </button>
        </form>
    </main>
</template>

<style scoped>
.invite-page {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8vw;
    align-items: center;
    padding: 8vw;
    background:
        radial-gradient(circle at 20% 30%, #123e91, transparent 35%), #030814;
    color: #eff5ff;
}
.invite-page h1 {
    font:
        400 clamp(3rem, 7vw, 7rem)/0.9 Georgia,
        serif;
    letter-spacing: -0.055em;
}
.invite-page section > p:last-child {
    max-width: 520px;
    color: #93a2bb;
    line-height: 1.7;
}
.invite-page form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 2rem;
    border: 1px solid rgba(126, 167, 227, 0.18);
    border-radius: 16px;
    background: #071126;
}
.invite-page label {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    color: #8291a8;
    font-size: 0.7rem;
}
.invite-page input {
    padding: 0.9rem;
    border: 1px solid rgba(126, 167, 227, 0.2);
    border-radius: 8px;
    background: #030a18;
    color: white;
}
.invite-page small {
    color: #ff899a;
}
@media (max-width: 720px) {
    .invite-page {
        grid-template-columns: 1fr;
        padding: 2rem 1rem;
    }
}
</style>
