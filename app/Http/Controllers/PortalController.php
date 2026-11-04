<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ClientFile;
use App\Models\FinancialRecord;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function index(Request $request, string $locale): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->role === UserRole::Client && $user->client_organization_id, 403);

        $projects = Project::query()->where('client_organization_id', $user->client_organization_id)
            ->with([
                'tasks' => fn ($query) => $query->where('client_visible', true)->orderBy('due_at'),
                'financialRecords.payments',
                'files' => fn ($query) => $query->where('client_visible', true)->latest(),
            ])->latest()->get()->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'due_at' => $project->due_at?->toDateString(),
                'tasks' => $project->tasks->map->only(['id', 'title', 'description', 'priority', 'status', 'due_at']),
                'financial_records' => $project->financialRecords->map(fn (FinancialRecord $record) => [
                    'id' => $record->id,
                    'document_number' => $record->document_number,
                    'currency' => $record->currency,
                    'total' => $record->total,
                    'paid_amount' => $record->paid_amount,
                    'outstanding_amount' => $record->outstanding_amount,
                    'due_at' => $record->due_at->toDateString(),
                    'status' => $record->payment_status,
                ]),
                'files' => $project->files->map->only(['id', 'name', 'mime_type', 'size', 'created_at']),
            ]);

        return Inertia::render('portal/Dashboard', [
            'locale' => $locale,
            'organization' => $user->organization?->only(['id', 'name']),
            'projects' => $projects,
        ]);
    }

    public function storeFile(Request $request, string $locale): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->role === UserRole::Client && $user->client_organization_id, 403);

        $validated = $request->validate([
            'project_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,docx,xlsx,png,jpg,jpeg'],
        ]);

        $project = Project::query()
            ->where('client_organization_id', $user->client_organization_id)
            ->whereKey((int) $validated['project_id'])
            ->firstOrFail();
        $uploaded = $request->file('file');
        abort_unless($uploaded instanceof UploadedFile, 422);
        $path = $uploaded->store("clients/{$user->client_organization_id}/projects/{$project->id}", 'local');

        ClientFile::create([
            'project_id' => $project->id, 'uploaded_by' => $user->id, 'name' => $uploaded->getClientOriginalName(),
            'path' => $path, 'disk' => 'local', 'mime_type' => $uploaded->getMimeType() ?: 'application/octet-stream',
            'size' => $uploaded->getSize(), 'client_visible' => true,
        ]);

        return back()->with('success', 'File uploaded.');
    }

    public function download(Request $request, ClientFile $clientFile, string $locale): StreamedResponse
    {
        $user = $request->user();
        $clientFile->loadMissing('project');
        abort_unless($user instanceof User && $user->role === UserRole::Client && $clientFile->client_visible && $clientFile->project->client_organization_id === $user->client_organization_id, 403);

        return Storage::disk($clientFile->disk)->download($clientFile->path, $clientFile->name);
    }
}
