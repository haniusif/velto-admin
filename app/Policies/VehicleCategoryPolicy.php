<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VehicleCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VehicleCategory');
    }

    public function view(AuthUser $authUser, VehicleCategory $vehicleCategory): bool
    {
        return $authUser->can('View:VehicleCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VehicleCategory');
    }

    public function update(AuthUser $authUser, VehicleCategory $vehicleCategory): bool
    {
        return $authUser->can('Update:VehicleCategory');
    }

    public function delete(AuthUser $authUser, VehicleCategory $vehicleCategory): bool
    {
        return $authUser->can('Delete:VehicleCategory');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VehicleCategory');
    }

    public function restore(AuthUser $authUser, VehicleCategory $vehicleCategory): bool
    {
        return $authUser->can('Restore:VehicleCategory');
    }

    public function forceDelete(AuthUser $authUser, VehicleCategory $vehicleCategory): bool
    {
        return $authUser->can('ForceDelete:VehicleCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VehicleCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VehicleCategory');
    }

    public function replicate(AuthUser $authUser, VehicleCategory $vehicleCategory): bool
    {
        return $authUser->can('Replicate:VehicleCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VehicleCategory');
    }

}