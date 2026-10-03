<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\StoreRoomRequest;
use App\Http\Requests\Rooms\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class RoomController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $rooms = Room::query()
            ->with('hotel')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate();

        return RoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = Room::query()->create($request->validated());

        return (new RoomResource($room->load('hotel')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Room $room): RoomResource
    {
        return new RoomResource($room->load('hotel'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RoomResource
    {
        $room->update($request->validated());

        return new RoomResource($room->refresh()->load('hotel'));
    }

    public function destroy(Room $room): Response
    {
        if ($room->reservations()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir o quarto porque existem reservas vinculadas a ele.',
            ], Response::HTTP_CONFLICT);
        }

        $room->delete();

        return response()->noContent();
    }
}
