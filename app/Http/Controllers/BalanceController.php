<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use App\Services\OperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BalanceController extends Controller
{
    public function get(Request $request): Response
    {
        return Inertia::render('Balance/Index');
    }

    public function index(Request $request, BalanceService $balanceService, OperationService $operationService): JsonResponse
    {
        $user = $request->user(); // текущий аутентифицированный user
        $balance = $user ? $balanceService->getBalance($user) : 0.0;

        $operations = $operationService->getOperationsPaginated($user, 20);

        return response()->json([
            'balance' => $balance,
            'operations' => $operations,
        ]);
    }

    public function show(Request $request): Response
    {
        return Inertia::render('Balance/List');
    }

    public function list(Request $request, BalanceService $balanceService, OperationService $operationService): JsonResponse
    {
        $user = $request->user(); // текущий аутентифицированный user
        $balance = $user ? $balanceService->getBalance($user) : 0.0;

        $operations = $operationService->getLastOperations($user);

        return response()->json([
            'balance' => $balance,
            'operations' => $operations,
        ]);
    }
}
