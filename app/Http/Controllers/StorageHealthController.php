<?php

namespace App\Http\Controllers;

use App\Services\StorageService;
use Illuminate\Http\JsonResponse;

class StorageHealthController extends Controller
{
    public function show(StorageService $storage): JsonResponse
    {
        return response()->json($storage->testStorageConnections());
    }
}
