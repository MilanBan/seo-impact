<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Sign in',
        description: 'Use your client account to continue',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-emerald-400"
    >
        {{ status }}
    </div>

    <PasskeyVerify appearance="portal-dark" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        novalidate
        class="portal-login-form flex flex-col"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="email" class="text-sm font-semibold text-white"
                    >Email address</Label
                >
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                    :aria-invalid="Boolean(errors.email)"
                    class="h-11 rounded-lg border-white/20 bg-white/[0.06] px-3 text-base text-white shadow-sm transition-[border-color,background-color,box-shadow] duration-200 placeholder:text-zinc-400 hover:border-white/30 hover:bg-white/[0.08] focus-visible:border-blue-400 focus-visible:bg-white/[0.08] focus-visible:ring-2 focus-visible:ring-blue-500/30 md:text-sm"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label
                        for="password"
                        class="text-sm font-semibold text-white"
                        >Password</Label
                    >
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="rounded text-xs font-medium text-zinc-300 no-underline transition-colors duration-200 hover:text-blue-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500"
                    >
                        Forgot your password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Password"
                    :aria-invalid="Boolean(errors.password)"
                    class="h-11 rounded-lg border-white/20 bg-white/[0.06] px-3 text-base text-white shadow-sm transition-[border-color,background-color,box-shadow] duration-200 placeholder:text-zinc-400 hover:border-white/30 hover:bg-white/[0.08] focus-visible:border-blue-400 focus-visible:bg-white/[0.08] focus-visible:ring-2 focus-visible:ring-blue-500/30 md:text-sm"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <Label
                    for="remember"
                    class="flex cursor-pointer items-center gap-3 text-sm font-medium text-zinc-200"
                >
                    <Checkbox
                        id="remember"
                        name="remember"
                        class="size-5 rounded-[5px] border-white/25 bg-white/[0.06] shadow-sm focus-visible:ring-blue-500/30 data-[state=checked]:border-blue-500 data-[state=checked]:bg-blue-600"
                    />
                    <span>Remember me</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-1 h-11 w-full cursor-pointer rounded-lg border border-blue-500 bg-blue-600 text-sm font-semibold text-white shadow-sm transition-[background-color,box-shadow] duration-200 hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500/35 disabled:cursor-wait disabled:opacity-60"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Sign in
            </Button>
        </div>
    </Form>
</template>

<style scoped>
.portal-login-form :deep(input:-webkit-autofill),
.portal-login-form :deep(input:-webkit-autofill:hover),
.portal-login-form :deep(input:-webkit-autofill:focus) {
    -webkit-box-shadow: 0 0 0 1000px #27272a inset !important;
    -webkit-text-fill-color: #fff !important;
    caret-color: #fff;
}

.portal-login-form :deep(input:autofill) {
    box-shadow: 0 0 0 1000px #27272a inset !important;
    color: #fff !important;
    caret-color: #fff;
}
</style>
