<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WashPackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class WashPackagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WashPackage');
    }

    public function view(AuthUser $authUser, WashPackage $washPackage): bool
    {
        return $authUser->can('View:WashPackage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WashPackage');
    }

    public function update(AuthUser $authUser, WashPackage $washPackage): bool
    {
        return $authUser->can('Update:WashPackage');
    }

    public function delete(AuthUser $authUser, WashPackage $washPackage): bool
    {
        return $authUser->can('Delete:WashPackage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WashPackage');
    }

    public function restore(AuthUser $authUser, WashPackage $washPackage): bool
    {
        return $authUser->can('Restore:WashPackage');
    }

    public function forceDelete(AuthUser $authUser, WashPackage $washPackage): bool
    {
        return $authUser->can('ForceDelete:WashPackage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WashPackage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WashPackage');
    }

    public function replicate(AuthUser $authUser, WashPackage $washPackage): bool
    {
        return $authUser->can('Replicate:WashPackage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WashPackage');
    }

}