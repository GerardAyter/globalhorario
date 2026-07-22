<?php

namespace App\Http\Controllers\Api;

use App\Models\Document;
use App\Models\Employee;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends BaseController
{
    public function __construct(private NotificationService $notifications) {}

    /** Llista documents: HR+ veu tots els de l'empresa, la resta només els propis */
    public function index(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;

        if (! $companyId) return $this->success([]);

        $query = Document::where('company_id', $companyId)
            ->with(['employee:id,nom,cognoms', 'uploadedBy:id,name'])
            ->orderByDesc('created_at');

        if ($user->hasMinRole('hr')) {
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->integer('employee_id'));
            }
            if ($request->filled('type')) {
                $query->where('type', $request->string('type'));
            }
        } else {
            $employeeId = $user->employee?->id;
            if (! $employeeId) return $this->success([]);
            $query->where('employee_id', $employeeId);
        }

        return $this->success($query->get());
    }

    /** Pujar un document i enviar-lo a un o més empleats (HR+) */
    public function store(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;

        if (! $companyId) return $this->error('Empresa no trobada', null, 422);

        $data = $request->validate([
            'title'          => 'required|string|max:150',
            'description'    => 'nullable|string|max:2000',
            'type'           => 'required|in:' . implode(',', Document::TYPES),
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'required|integer|exists:employees,id',
            'file'           => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $employees = Employee::where('company_id', $companyId)
            ->whereIn('id', $data['employee_ids'])
            ->get();

        if ($employees->isEmpty()) {
            return $this->error('Cap empleat vàlid seleccionat', null, 422);
        }

        $file     = $request->file('file');
        $filePath = $file->store("documents/{$companyId}", 'local');

        $documents = [];
        foreach ($employees as $employee) {
            $document = Document::create([
                'company_id'  => $companyId,
                'employee_id' => $employee->id,
                'uploaded_by' => $user->id,
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'type'        => $data['type'],
                'file_path'   => $filePath,
                'file_name'   => $file->getClientOriginalName(),
                'file_size'   => $file->getSize(),
                'mime_type'   => $file->getClientMimeType(),
            ]);
            $documents[] = $document;

            if ($employee->user_id) {
                $this->notifications->send(
                    $employee->user_id,
                    'document',
                    'Nou document: ' . $data['title'],
                    'Se t\'ha enviat un nou document.',
                    ['document_id' => $document->id]
                );
            }
        }

        return $this->success($documents, 'Document enviat correctament', null, 201);
    }

    /** Descarregar un document (propietari o HR+ de la mateixa empresa) */
    public function download(Request $request, int $id)
    {
        $user     = $request->user();
        $document = Document::find($id);

        if (! $document) return $this->error('Document not found', null, 404);

        $companyId  = $user->company_id ?? $user->employee?->company_id;
        $isOwner    = $user->employee?->id === $document->employee_id;
        $isManager  = $user->hasMinRole('hr') && $companyId === $document->company_id;

        if (! $isOwner && ! $isManager) {
            return $this->error('Accés denegat', null, 403);
        }

        if ($isOwner && ! $document->read_at) {
            $document->update(['read_at' => now()]);
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            return $this->error('Fitxer no trobat', null, 404);
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    /** Eliminar un document (HR+) */
    public function destroy(Request $request, int $id)
    {
        $user     = $request->user();
        $document = Document::find($id);

        if (! $document) return $this->error('Document not found', null, 404);

        $companyId = $user->company_id ?? $user->employee?->company_id;
        if ($companyId !== $document->company_id) {
            return $this->error('Accés denegat', null, 403);
        }

        $filePath        = $document->file_path;
        $otherReferences = Document::where('file_path', $filePath)
            ->where('id', '!=', $document->id)
            ->exists();

        $document->delete();

        if (! $otherReferences) {
            Storage::disk('local')->delete($filePath);
        }

        return $this->success(null, 'Document eliminat');
    }
}
