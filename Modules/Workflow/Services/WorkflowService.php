<?php

declare(strict_types=1);

namespace Modules\Workflow\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Exceptions\TenantResolutionException;
use Modules\Workflow\Contracts\WorkflowServiceContract;
use Modules\Workflow\DTOs\CreateWorkflowDTO;
use Modules\Workflow\DTOs\ListWorkflowsDTO;
use Modules\Workflow\DTOs\UpdateWorkflowDTO;
use Modules\Workflow\Models\Workflow;

class WorkflowService implements WorkflowServiceContract
{
    public function __construct(
        private readonly TenantContextContract $tenantContext,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Workflow>
     */
    public function paginate(ListWorkflowsDTO $filters): LengthAwarePaginator
    {
        $query = Workflow::query();

        if ($filters->status !== null) {
            $query->where('status', $filters->status);
        }

        if ($filters->search !== null && $filters->search !== '') {
            $search = '%'.$filters->search.'%';
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', $search)
                    ->orWhere('slug', 'like', $search);
            });
        }

        $sortColumn = in_array($filters->sort, ['name', 'slug', 'created_at', 'updated_at', 'status'], true)
            ? $filters->sort
            : 'created_at';

        return $query
            ->orderBy($sortColumn, $filters->direction === 'asc' ? 'asc' : 'desc')
            ->paginate(
                perPage: $filters->perPage,
                page: $filters->page,
            );
    }

    public function create(CreateWorkflowDTO $dto): Workflow
    {
        $tenantId = $this->tenantContext->tenantId();

        if ($tenantId === null) {
            throw TenantResolutionException::notProvided();
        }

        $slug = $dto->slug ?? $this->generateUniqueSlug($dto->name, $tenantId);

        return Workflow::query()->create([
            'name' => $dto->name,
            'slug' => $slug,
            'description' => $dto->description,
            'status' => $dto->status,
            'created_by' => $dto->createdBy,
        ]);
    }

    public function update(Workflow $workflow, UpdateWorkflowDTO $dto): Workflow
    {
        if ($dto->name !== null) {
            $workflow->name = $dto->name;
        }

        if ($dto->slug !== null) {
            $workflow->slug = $dto->slug;
        }

        if ($dto->description !== null) {
            $workflow->description = $dto->description;
        }

        if ($dto->status !== null) {
            $workflow->status = $dto->status;
        }

        $workflow->save();

        return $workflow->refresh();
    }

    public function delete(Workflow $workflow): void
    {
        $workflow->delete();
    }

    private function generateUniqueSlug(string $name, ?string $tenantId): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'workflow';
        $suffix = 1;

        while ($this->slugExists($slug, $tenantId)) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?string $tenantId, ?string $exceptWorkflowId = null): bool
    {
        $query = Workflow::query()->where('slug', $slug);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        if ($exceptWorkflowId !== null) {
            $query->where('id', '!=', $exceptWorkflowId);
        }

        return $query->exists();
    }
}
