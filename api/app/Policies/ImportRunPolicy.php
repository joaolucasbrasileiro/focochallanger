<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ImportRun;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ImportRunPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $this->viewImports($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ImportRun $importRun): Response
    {
        return $this->viewImports($user);
    }

    private function viewImports(User $user): Response
    {
        return $user->hasPermissionInAnyHotel(Permission::ViewImports)
            ? Response::allow()
            : Response::deny('Você não tem permissão para consultar importações.');
    }
}
