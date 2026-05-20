<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Worker;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Worker');
    }

    public function view(AuthUser $authUser, Worker $worker): bool
    {
        return $authUser->can('View:Worker');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Worker');
    }

    public function update(AuthUser $authUser, Worker $worker): bool
    {
        return $authUser->can('Update:Worker');
    }

    public function delete(AuthUser $authUser, Worker $worker): bool
    {
        return $authUser->can('Delete:Worker');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Worker');
    }

    public function restore(AuthUser $authUser, Worker $worker): bool
    {
        return $authUser->can('Restore:Worker');
    }

    public function forceDelete(AuthUser $authUser, Worker $worker): bool
    {
        return $authUser->can('ForceDelete:Worker');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Worker');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Worker');
    }

    public function replicate(AuthUser $authUser, Worker $worker): bool
    {
        return $authUser->can('Replicate:Worker');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Worker');
    }

}