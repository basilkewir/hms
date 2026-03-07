<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HotelService;
use App\Models\BreakfastMenu;
use Illuminate\Http\Request;

class ReservationServiceController extends Controller
{
    public function getAvailableServices()
    {
        $services = HotelService::where('is_active', true)
            ->where('available_online', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'services' => $services,
        ]);
    }

    public function getBreakfastMenus()
    {
        $menus = BreakfastMenu::where('is_active', true)
            ->where('available_online', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'menus' => $menus,
        ]);
    }

    public function getAllAvailableOptions()
    {
        $services = HotelService::where('is_active', true)
            ->where('available_online', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $breakfastMenus = BreakfastMenu::where('is_active', true)
            ->where('available_online', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'services' => $services,
                'breakfast_menus' => $breakfastMenus,
            ],
        ]);
    }
}
