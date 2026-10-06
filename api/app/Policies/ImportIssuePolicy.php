<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Hotel;
use App\Models\ImportIssue;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ImportIssuePolicy
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
    public function view(User $user, ImportIssue $importIssue): Response
    {
        $hotelExternalId = $importIssue->metadata['hotel_external_id'] ?? null;

        if (! is_string($hotelExternalId) || ! ctype_digit($hotelExternalId)) {
            return Response::deny('Não foi possível determinar o hotel desta pendência.');
        }

        $hotel = Hotel::query()->where('external_id', (int) $hotelExternalId)->first();

        return $hotel !== null && $user->hasPermissionForHotel($hotel, Permission::ViewImports)
            ? Response::allow()
            : Response::deny('Você não tem permissão para consultar esta pendência de importação.');
    }

    private function viewImports(User $user): Response
    {
        return $user->hasPermissionInAnyHotel(Permission::ViewImports)
            ? Response::allow()
            : Response::deny('Você não tem permissão para consultar pendências de importação.');
    }
}
