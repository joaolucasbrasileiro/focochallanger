<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HotelController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $hotels = Hotel::query()
            ->orderBy('name')
            ->orderBy('id')
            ->paginate();

        return HotelResource::collection($hotels);
    }
}
