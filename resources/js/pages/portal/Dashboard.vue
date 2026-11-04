<script setup lang="ts">
import LogoMark from '@/components/brand/LogoMark.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Task {
    id: number;
    title: string;
    description?: string;
    priority: string;
    status: string;
    due_at?: string;
}
interface Invoice {
    id: number;
    document_number: string;
    currency: string;
    total: string;
    paid_amount: number;
    outstanding_amount: number;
    due_at?: string;
    status: string;
}
interface ClientFile {
    id: number;
    name: string;
    mime_type: string;
    size: number;
    created_at: string;
}
interface Project {
    id: number;
    name: string;
    description?: string;
    status: string;
    due_at?: string;
    tasks: Task[];
    financial_records: Invoice[];
    files: ClientFile[];
}

const props = defineProps<{
    locale: string;
    organization: { id: number; name: string };
    projects: Project[];
}>();
const upload = useForm<{ project_id: number | null; file: File | null }>({
    project_id: props.projects[0]?.id ?? null,
    file: null,
});
const money = (value: number | string, currency: string) =>
    new Intl.NumberFormat(
        props.locale === 'sr'
            ? 'sr-RS'
            : props.locale === 'de'
              ? 'de-DE'
              : 'en-GB',
        { style: 'currency', currency },
    ).format(Number(value));
const formatSize = (bytes: number) => `${(bytes / 1024 / 1024).toFixed(1)} MB`;
const submitUpload = () =>
    upload.post(`/${props.locale}/portal/files`, {
        preserveScroll: true,
        onSuccess: () => upload.reset('file'),
    });
</script>

<template>
    <Head title="Client Portal"
        ><meta name="robots" content="noindex,nofollow"
    /></Head>
    <div class="portal-shell">
        <aside class="portal-sidebar">
            <Link :href="`/${locale}`"><LogoMark /></Link>
            <nav>
                <a href="#overview" class="active">Overview</a
                ><a href="#tasks">Tasks</a><a href="#billing">Billing</a
                ><a href="#files">Files</a>
            </nav>
            <div class="portal-org">
                <small>ORGANIZATION</small
                ><strong>{{ organization.name }}</strong
                ><Link href="/settings/profile">Account settings ↗</Link>
            </div>
        </aside>
        <main class="portal-main">
            <header>
                <div>
                    <p class="eyebrow"><span></span>CLIENT PORTAL</p>
                    <h1>Good to see you.</h1>
                    <p>
                        Everything important across projects, delivery and
                        billing.
                    </p>
                </div>
                <Link :href="`/${locale}`" class="button button--small"
                    >Visit website ↗</Link
                >
            </header>
            <section id="overview" class="portal-kpis">
                <article>
                    <small>Active projects</small
                    ><strong>{{ projects.length }}</strong>
                </article>
                <article>
                    <small>Open tasks</small
                    ><strong>{{
                        projects.reduce(
                            (sum, p) =>
                                sum +
                                p.tasks.filter((t) => t.status !== 'done')
                                    .length,
                            0,
                        )
                    }}</strong>
                </article>
                <article>
                    <small>Outstanding</small
                    ><strong>{{
                        money(
                            projects
                                .flatMap((p) => p.financial_records)
                                .reduce(
                                    (sum, i) =>
                                        sum + Number(i.outstanding_amount),
                                    0,
                                ),
                            projects[0]?.financial_records[0]?.currency ||
                                'EUR',
                        )
                    }}</strong>
                </article>
            </section>
            <section
                v-for="project in projects"
                :key="project.id"
                class="portal-project"
            >
                <div class="project-title">
                    <div>
                        <span class="status-dot"></span
                        ><small>{{ project.status }}</small>
                        <h2>{{ project.name }}</h2>
                        <p>{{ project.description }}</p>
                    </div>
                    <time v-if="project.due_at"
                        >Target {{ project.due_at }}</time
                    >
                </div>
                <div class="portal-columns">
                    <div id="tasks" class="portal-panel">
                        <div class="panel-heading">
                            <h3>Visible tasks</h3>
                            <span>{{ project.tasks.length }}</span>
                        </div>
                        <div
                            v-for="task in project.tasks"
                            :key="task.id"
                            class="task-row"
                        >
                            <i :class="task.status"></i>
                            <div>
                                <strong>{{ task.title }}</strong
                                ><small
                                    >{{ task.priority
                                    }}<template v-if="task.due_at">
                                        · {{ task.due_at }}</template
                                    ></small
                                >
                            </div>
                            <span>{{ task.status.replace('_', ' ') }}</span>
                        </div>
                    </div>
                    <div id="billing" class="portal-panel">
                        <div class="panel-heading"><h3>Billing</h3></div>
                        <div
                            v-for="invoice in project.financial_records"
                            :key="invoice.id"
                            class="invoice-card"
                        >
                            <div>
                                <small>{{ invoice.document_number }}</small
                                ><strong>{{
                                    money(invoice.total, invoice.currency)
                                }}</strong>
                            </div>
                            <div class="invoice-progress">
                                <span
                                    :style="{
                                        width: `${Math.min(100, (Number(invoice.paid_amount) / Number(invoice.total)) * 100)}%`,
                                    }"
                                ></span>
                            </div>
                            <p>
                                {{
                                    money(
                                        invoice.outstanding_amount,
                                        invoice.currency,
                                    )
                                }}
                                outstanding · {{ invoice.status }}
                            </p>
                        </div>
                    </div>
                </div>
                <div id="files" class="portal-panel files-panel">
                    <div class="panel-heading">
                        <h3>Private files</h3>
                        <span>PDF, DOCX, XLSX, PNG, JPG · 20 MB</span>
                    </div>
                    <div class="file-list">
                        <a
                            v-for="file in project.files"
                            :key="file.id"
                            :href="`/${locale}/portal/files/${file.id}`"
                            ><span>▧</span>
                            <div>
                                <strong>{{ file.name }}</strong
                                ><small>{{ formatSize(file.size) }}</small>
                            </div>
                            <b>↓</b></a
                        >
                    </div>
                    <form class="upload-form" @submit.prevent="submitUpload">
                        <input
                            type="hidden"
                            name="project_id"
                            :value="project.id"
                        /><input
                            type="file"
                            required
                            accept=".pdf,.docx,.xlsx,.png,.jpg,.jpeg"
                            @input="
                                upload.file =
                                    ($event.target as HTMLInputElement)
                                        .files?.[0] || null;
                                upload.project_id = project.id;
                            "
                        /><button
                            class="button button--small"
                            :disabled="upload.processing"
                        >
                            {{
                                upload.processing ? 'Uploading…' : 'Upload file'
                            }}
                        </button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</template>
