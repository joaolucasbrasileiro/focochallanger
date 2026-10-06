<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\ListRoomsRequest;
use App\Http\Requests\Rooms\StoreRoomRequest;
use App\Http\Requests\Rooms\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class RoomController extends Controller
{
    public function index(ListRoomsRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Room::class);

        $filters = $request->validated();

        $rooms = Room::query()
            ->whereIn('hotel_id', $request->user()->hotelIdsWithPermission(Permission::ViewRooms))
            ->when(
                isset($filters['hotel_id']),
                fn ($query) => $query->where('hotel_id', $filters['hotel_id']),
            )
            ->when(
                isset($filters['name']),
                fn ($query) => $query->where('name', 'like', "%{$filters['name']}%"),
            )
            ->when(
                isset($filters['is_active']),
                fn ($query) => $query->where('is_active', $filters['is_active']),
            )
            ->with('hotel')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return RoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $data = $request->validated();
        $hotel = Hotel::query()->findOrFail($data['hotel_id']);

        $this->authorize('create', [Room::class, $hotel]);

        $room = Room::query()->create($data);

        return (new RoomResource($room->load('hotel')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Room $room): RoomResource
    {
        $this->authorize('view', $room);

        return new RoomResource($room->load('hotel'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RoomResource
    {
        $this->authorize('update', $room);
        $data = $request->validated();

        if (isset($data['hotel_id']) && $data['hotel_id'] !== $room->hotel_id) {
            $targetHotel = Hotel::query()->findOrFail($data['hotel_id']);
            $this->authorize('create', [Room::class, $targetHotel]);
        }

        $room->update($data);

        return new RoomResource($room->refresh()->load('hotel'));
    }

    public function destroy(Room $room): Response
    {
        $this->authorize('delete', $room);

        if ($room->reservations()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir o quarto porque existem reservas vinculadas a ele.',
            ], Response::HTTP_CONFLICT);
        }

        $room->delete();

        return response()->noContent();
    }
}
