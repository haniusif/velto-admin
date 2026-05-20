<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VehicleColor;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleColorPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VehicleColor');
    }

    public function view(AuthUser $authUser, VehicleColor $vehicleColor): bool
    {
        return $authUser->can('View:VehicleColor');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VehicleColor');
    }

    public function update(AuthUser $authUser, VehicleColor $vehicleColor): bool
    {
        return $authUser->can('Update:VehicleColor');
    }

    public function delete(AuthUser $authUser, VehicleColor $vehicleColor): bool
    {
        return $authUser->can('Delete:VehicleColor');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VehicleColor');
    }

    public function restore(AuthUser $authUser, VehicleColor $vehicleColor): bool
    {
        return $authUser->can('Restore:VehicleColor');
    }

    public function forceDelete(AuthUser $authUser, VehicleColor $vehicleColor): bool
    {
        return $authUser->can('ForceDelete:VehicleColor');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VehicleColor');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VehicleColor');
    }

    public function replicate(AuthUser $authUser, VehicleColor $vehicleColor): bool
    {
        return $authUser->can('Replicate:VehicleColor');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VehicleColor');
    }

}