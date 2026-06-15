<?php

namespace App\Services;

use App\Models\AbsenceRequest;

class AbsenceRequestService extends BaseService
{
    /**
     * List absence requests with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return AbsenceRequest::query()->paginate(20);
    }

    /**
     * Find an absence request by id.
     *
     * @param int $id
     * @return AbsenceRequest|null
     */
    public function find(int $id): ?AbsenceRequest
    {
        return AbsenceRequest::find($id);
    }

    /**
     * Create a new absence request.
     *
     * @param array $data
     * @return AbsenceRequest
     */
    public function create(array $data): AbsenceRequest
    {
        return AbsenceRequest::create($data);
    }

    /**
     * Update an absence request.
     *
     * @param AbsenceRequest $item
     * @param array $data
     * @return AbsenceRequest
     */
    public function update(AbsenceRequest $item, array $data): AbsenceRequest
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete an absence request.
     *
     * @param AbsenceRequest $item
     * @return bool
     */
    public function delete(AbsenceRequest $item): bool
    {
        return $item->delete();
    }

    /**
     * Approve an absence request.
     *
     * @param AbsenceRequest $item
     * @return AbsenceRequest
     */
    public function approve(AbsenceRequest $item): AbsenceRequest
    {
        $item->status = AbsenceRequest::STATUS_APPROVED;
        $item->save();
        return $item;
    }

    /**
     * Deny an absence request.
     *
     * @param AbsenceRequest $item
     * @return AbsenceRequest
     */
    public function deny(AbsenceRequest $item): AbsenceRequest
    {
        $item->status = AbsenceRequest::STATUS_DENIED;
        $item->save();
        return $item;
    }
}
