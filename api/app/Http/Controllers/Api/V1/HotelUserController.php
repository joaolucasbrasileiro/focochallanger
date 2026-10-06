<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\HotelUsers\StoreHotelUserRequest;
use App\Http\Requests\HotelUsers\UpdateHotelUserRequest;
use App\Http\Resources\HotelMembershipResource;
use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HotelUserController extends Controller
{
    public function index(Hotel $hotel): AnonymousResourceCollection
    {
        $this->authorize('manageUsers', $hotel);

        return HotelMembershipResource::collection(
            $hotel->memberships()
                ->with(['hotel', 'user'])
                ->orderBy('id')
                ->paginate(),
        );
    }

    public function store(StoreHotelUserRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('manageUsers', $hotel);
        $data = $request->validated();

        $membership = DB::transaction(function () use ($data, $hotel): HotelMembership {
            $user = User::query()->where('email', $data['email'])->first();

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'email_verified_at' => now(),
                ]);
            }

            return $hotel->memberships()->create([
                'user_id' => $user->id,
                'role' => UserRole::from($data['role']),
            ]);
        });

        return (new HotelMembershipResource($membership->load(['hotel', 'user'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateHotelUserRequest $request, Hotel $hotel, User $user): HotelMembershipResource|JsonResponse
    {
        $this->authorize('manageUsers', $hotel);
        $membership = $this->membership($hotel, $user);
        $role = UserRole::from($request->validated('role'));

        if ($this->wouldRemoveLastAdmin($hotel, $membership, $role)) {
            return response()->json([
                'message' => 'O hotel deve possuir pelo menos um administrador.',
            ], Response::HTTP_CONFLICT);
        }

        $membership->update(['role' => $role]);

        return new HotelMembershipResource($membership->refresh()->load(['hotel', 'user']));
    }

    public function destroy(Hotel $hotel, User $user): Response|JsonResponse
    {
        $this->authorize('manageUsers', $hotel);
        $membership = $this->membership($hotel, $user);

        if ($this->wouldRemoveLastAdmin($hotel, $membership)) {
            return response()->json([
                'message' => 'O hotel deve possuir pelo menos um administrador.',
            ], Response::HTTP_CONFLICT);
        }

        $membership->delete();

        return response()->noContent();
    }

    private function membership(Hotel $hotel, User $user): HotelMembership
    {
        return $hotel->memberships()
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    private function wouldRemoveLastAdmin(
        Hotel $hotel,
        HotelMembership $membership,
        ?UserRole $newRole = null,
    ): bool {
        if ($membership->role !== UserRole::Admin || $newRole === UserRole::Admin) {
            return false;
        }

        return $hotel->memberships()
            ->where('role', UserRole::Admin->value)
            ->count() === 1;
    }
}
