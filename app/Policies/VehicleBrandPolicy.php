<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VehicleBrand;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleBrandPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VehicleBrand');
    }

    public function view(AuthUser $authUser, VehicleBrand $vehicleBrand): bool
    {
        return $authUser->can('View:VehicleBrand');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VehicleBrand');
    }

    public function update(AuthUser $authUser, VehicleBrand $vehicleBrand): bool
    {
        return $authUser->can('Update:VehicleBrand');
    }

    public function delete(AuthUser $authUser, VehicleBrand $vehicleBrand): bool
    {
        return $authUser->can('Delete:VehicleBrand');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VehicleBrand');
    }

    public function restore(AuthUser $authUser, VehicleBrand $vehicleBrand): bool
    {
        return $authUser->can('Restore:VehicleBrand');
    }

    public function forceDelete(AuthUser $authUser, VehicleBrand $vehicleBrand): bool
    {
        return $authUser->can('ForceDelete:VehicleBrand');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VehicleBrand');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VehicleBrand');
    }

    public function replicate(AuthUser $authUser, VehicleBrand $vehicleBrand): bool
    {
        return $authUser->can('Replicate:VehicleBrand');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VehicleBrand');
    }

}