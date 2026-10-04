<?php

namespace App\Services\Reservations;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Collection;

class RoomAvailabilityService
{
    /**
     * @return Collection<int, array{name: string, total_units: int, available_units: int}>
     */
    public function forHotel(Hotel $hotel, string $checkIn, string $checkOut): Collection
    {
        $rooms = $this->matchingRooms($hotel);
        $occupiedRoomIds = $this->occupiedRoomIds($rooms, $checkIn, $checkOut);

        return $rooms
            ->groupBy('name')
            ->map(function (Collection $rooms, string $name) use ($occupiedRoomIds): array {
                return [
                    'name' => $name,
                    'total_units' => $rooms->count(),
                    'available_units' => $rooms
                        ->reject(fn (Room $room): bool => $occupiedRoomIds->has($room->id))
                        ->count(),
                ];
            })
            ->values();
    }

    public function firstAvailableRoom(
        Hotel $hotel,
        string $roomName,
        string $checkIn,
        string $checkOut,
    ): ?Room {
        $rooms = $this->matchingRooms($hotel, $roomName, true);
        $occupiedRoomIds = $this->occupiedRoomIds($rooms, $checkIn, $checkOut);

        return $rooms->first(fn (Room $room): bool => ! $occupiedRoomIds->has($room->id));
    }

    /**
     * @return Collection<int, Room>
     */
    private function matchingRooms(Hotel $hotel, ?string $roomName = null, bool $lockForUpdate = false): Collection
    {
        $query = Room::query()
            ->where('hotel_id', $hotel->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('id');

        if ($roomName !== null) {
            $query->where('name', $roomName);
        }

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    /**
     * @param  Collection<int, Room>  $rooms
     * @return Collection<int, true>
     */
    private function occupiedRoomIds(Collection $rooms, string $checkIn, string $checkOut): Collection
    {
        if ($rooms->isEmpty()) {
            return collect();
        }

        return Reservation::query()
            ->whereIn('room_id', $rooms->modelKeys())
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->distinct()
            ->pluck('room_id')
            ->mapWithKeys(fn (int $roomId): array => [$roomId => true]);
    }
}
