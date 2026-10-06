<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\StoreRoomRequest;
use App\Http\Requests\Rooms\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class RoomController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::query()
            ->whereIn('hotel_id', $request->user()->hotelIdsWithPermission(Permission::ViewRooms))
            ->with('hotel')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate();

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
