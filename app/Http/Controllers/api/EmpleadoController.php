<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmpleadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    public function __construct(private EmpleadoService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->getAll(
                $request->query('cargo'),
                $request->query('sucursal_id') ? (int) $request->query('sucursal_id') : null
            )
        );
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sucursal_id'      => 'required|exists:sucursales,id',
            'user_id'          => 'nullable|exists:users,id',
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'ci'               => 'required|string|max:20|unique:empleados,ci',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:100',
            'direccion'        => 'nullable|string|max:200',
            'fecha_nacimiento' => 'nullable|date',
            'fecha_ingreso'    => 'required|date',
            'cargo'            => 'required|in:gerente,cajero,mozo,cocinero,ayudante',
            'turno'            => 'in:mañana,tarde,noche,completo',
            'salario'          => 'nullable|numeric|min:0',
            'foto'             => 'nullable|image|max:2048',
            'activo'           => 'boolean',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('empleados', 'public');
        }

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'sucursal_id'      => 'sometimes|exists:sucursales,id',
            'user_id'          => 'nullable|exists:users,id',
            'nombres'          => 'sometimes|string|max:100',
            'apellidos'        => 'sometimes|string|max:100',
            'ci'               => "sometimes|string|max:20|unique:empleados,ci,{$id}",
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:100',
            'direccion'        => 'nullable|string|max:200',
            'fecha_nacimiento' => 'nullable|date',
            'fecha_ingreso'    => 'sometimes|date',
            'cargo'            => 'sometimes|in:gerente,cajero,mozo,cocinero,ayudante',
            'turno'            => 'in:mañana,tarde,noche,completo',
            'salario'          => 'nullable|numeric|min:0',
            'foto'             => 'nullable|image|max:2048',
            'activo'           => 'boolean',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('empleados', 'public');
        }

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Empleado eliminado correctamente.']);
    }
}