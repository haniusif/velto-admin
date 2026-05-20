<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LegalPage;
use Illuminate\Auth\Access\HandlesAuthorization;

class LegalPagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LegalPage');
    }

    public function view(AuthUser $authUser, LegalPage $legalPage): bool
    {
        return $authUser->can('View:LegalPage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LegalPage');
    }

    public function update(AuthUser $authUser, LegalPage $legalPage): bool
    {
        return $authUser->can('Update:LegalPage');
    }

    public function delete(AuthUser $authUser, LegalPage $legalPage): bool
    {
        return $authUser->can('Delete:LegalPage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LegalPage');
    }

    public function restore(AuthUser $authUser, LegalPage $legalPage): bool
    {
        return $authUser->can('Restore:LegalPage');
    }

    public function forceDelete(AuthUser $authUser, LegalPage $legalPage): bool
    {
        return $authUser->can('ForceDelete:LegalPage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LegalPage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LegalPage');
    }

    public function replicate(AuthUser $authUser, LegalPage $legalPage): bool
    {
        return $authUser->can('Replicate:LegalPage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LegalPage');
    }

}