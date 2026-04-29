<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['sucursal_id', 'rol', 'activo']);
        if (isset($filters['sucursal_id'])) $filters['sucursal_id'] = (int) $filters['sucursal_id'];
        if (isset($filters['activo']))      $filters['activo']      = filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN);

        return response()->json($this->service->getAll($filters));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'rol'         => 'required|exists:roles,name',
            'activo'      => 'boolean',
        ]);

        try {
            return response()->json($this->service->create($data), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'email'       => "sometimes|email|unique:users,email,{$id}",
            'password'    => 'nullable|string|min:6',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'rol'         => 'sometimes|exists:roles,name',
            'activo'      => 'boolean',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return response()->json(['message' => 'Usuario eliminado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function roles(): JsonResponse
    {
        return response()->json($this->service->getRolesDisponibles());
    }
}