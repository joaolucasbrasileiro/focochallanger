<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotels\ListHotelsRequest;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HotelController extends Controller
{
    public function index(ListHotelsRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $hotels = Hotel::query()
            ->when(
                isset($filters['name']),
                fn ($query) => $query->where('name', 'like', "%{$filters['name']}%"),
            )
            ->when(
                isset($filters['external_id']),
                fn ($query) => $query->where('external_id', $filters['external_id']),
            )
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return HotelResource::collection($hotels);
    }
}
