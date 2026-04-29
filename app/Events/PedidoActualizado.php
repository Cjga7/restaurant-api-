<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PedidoActualizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Pedido $pedido;
    public string $accion;

    public function __construct(Pedido $pedido, string $accion = 'actualizado')
    {
        $this->pedido = $pedido->load(['mesa', 'mozo', 'sucursal', 'items']);
        $this->accion = $accion;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("pedidos.sucursal.{$this->pedido->sucursal_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pedido.actualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'accion' => $this->accion,
            'pedido' => $this->pedido->toArray(),
        ];
    }
}