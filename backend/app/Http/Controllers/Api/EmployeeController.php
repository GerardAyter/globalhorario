<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Notifications\EmployeeInvitation;
use App\Services\EmployeeService;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class EmployeeController extends BaseController
{
    protected EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;
        $filters   = $companyId ? ['company_id' => $companyId] : [];

        $data = $this->service->list($filters);
        return $this->success($data);
    }

    public function show($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Employee not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(EmployeeStoreRequest $request)
    {
        $authUser  = $request->user();
        $companyId = $authUser->company_id ?? $authUser->employee?->company_id;

        if (! $companyId) {
            return $this->error("El vostre compte no té cap empresa associada", null, 403);
        }

        $data               = $request->validated();
        $data['company_id'] = $companyId;

        $employee = $this->service->create($data);

        // Auto-create user account and send invitation if email provided
        if (! empty($data['email'])) {
            $existingUser = User::where('email', $data['email'])->first();

            if (! $existingUser) {
                $newUser = User::create([
                    'tenant_id'  => $employee->tenant_id,
                    'company_id' => $companyId,
                    'name'       => trim(($data['nom'] ?? '') . ' ' . ($data['cognoms'] ?? '')),
                    'email'      => $data['email'],
                    'password'   => Hash::make(Str::random(32)),
                    'role'       => User::ROLE_USER,
                ]);
                $employee->update(['user_id' => $newUser->id]);

                $token = Password::broker()->createToken($newUser);
                $newUser->notify(new EmployeeInvitation($token));
            } else {
                $employee->update(['user_id' => $existingUser->id]);
            }
        }

        return $this->success($this->service->find($employee->id), 'Employee created', null, 201);
    }

    public function update(EmployeeUpdateRequest $request, int $id)
    {
        $item = $this->service->find($id);
        if (! $item) {
            return $this->error('Employee not found', null, 404);
        }
        $item = $this->service->update($item, $request->validated());
        return $this->success($item, 'Employee updated');
    }

    public function destroy(int $id)
    {
        $item = $this->service->find($id);
        if (! $item) {
            return $this->error('Employee not found', null, 404);
        }

        if ($item->user && $item->user->role === 'admin') {
            return $this->error("No es pot eliminar l'empleat administrador de l'empresa", null, 403);
        }

        $this->service->delete($item);
        return $this->success(null, 'Employee deleted', null, 204);
    }

    public function sendInvitation(int $id)
    {
        $employee = $this->service->find($id);
        if (! $employee) {
            return $this->error('Employee not found', null, 404);
        }

        $user = $employee->user;
        if (! $user) {
            return $this->error("Aquest empleat no té compte d'usuari", null, 422);
        }

        try {
            $token = Password::broker()->createToken($user);
            $user->notify(new EmployeeInvitation($token));
        } catch (\Throwable $e) {
            \Log::error('Error enviant invitació: ' . $e->getMessage());
            return $this->error("No s'ha pogut enviar el correu: " . $e->getMessage(), null, 500);
        }

        return $this->success(null, 'Correu enviat correctament');
    }

    public function timeEntries(int $id)
    {
        $data = $this->service->timeEntries((int)$id);
        if ($data === null) {
            return $this->error('Employee not found', null, 404);
        }
        return $this->success($data);
    }
}
