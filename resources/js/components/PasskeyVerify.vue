<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { usePasskeyVerify } from '@laravel/passkeys/vue';
import { KeyRound } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    routes?: {
        options: UrlMethodPair;
        submit: UrlMethodPair;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
    appearance?: 'default' | 'portal-dark';
};

const props = defineProps<Props>();

const { verify, isLoading, error, isSupported } = usePasskeyVerify({
    ...(props.routes
        ? {
              routes: {
                  options: props.routes.options.url,
                  submit: props.routes.submit.url,
              },
          }
        : {}),
    onSuccess: (response) => {
        router.visit(response.redirect ?? '/dashboard');
    },
});
</script>

<template>
    <div v-if="isSupported">
        <div class="grid gap-2">
            <Button
                type="button"
                variant="outline"
                :class="[
                    'w-full',
                    appearance === 'portal-dark'
                        ? 'h-11 cursor-pointer rounded-lg border-white/20 bg-white/[0.06] text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] transition-[border-color,background-color,color,box-shadow] duration-200 hover:border-blue-400/55 hover:bg-blue-500/12 hover:text-blue-100 hover:shadow-[0_0_0_1px_rgba(59,130,246,0.1)] focus-visible:border-blue-400 focus-visible:ring-2 focus-visible:ring-blue-500/30 active:bg-blue-500/15 disabled:cursor-wait disabled:opacity-60'
                        : '',
                ]"
                @click="verify"
                :disabled="isLoading"
            >
                <Spinner v-if="isLoading" />
                <KeyRound
                    v-else
                    :class="[
                        'h-4 w-4',
                        appearance === 'portal-dark' ? 'text-blue-400' : '',
                    ]"
                />
                {{
                    isLoading
                        ? (props.loadingLabel ?? 'Authenticating...')
                        : (props.label ?? 'Sign in with a passkey')
                }}
            </Button>

            <div v-if="error" class="text-center">
                <InputError :message="error" />
            </div>
        </div>

        <div
            :class="[
                'relative',
                appearance === 'portal-dark' ? 'my-5' : 'my-6',
            ]"
        >
            <div class="absolute inset-0 flex items-center">
                <Separator
                    :class="[
                        'w-full',
                        appearance === 'portal-dark' ? 'bg-white/15' : '',
                    ]"
                />
            </div>
            <div
                :class="[
                    'relative flex justify-center text-xs uppercase',
                    appearance === 'portal-dark'
                        ? 'font-medium tracking-[0.08em] text-zinc-400'
                        : '',
                ]"
            >
                <span
                    :class="[
                        'px-3',
                        appearance === 'portal-dark'
                            ? 'bg-[#18181b] text-zinc-400'
                            : 'bg-background text-muted-foreground',
                    ]"
                >
                    {{ props.separator ?? 'Or continue with email' }}
                </span>
            </div>
        </div>
    </div>
</template>
