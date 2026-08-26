<?php
namespace App\Reservas\Mapper;

use App\Reservas\DTOs\Request\ActualizarReservaRequest;
use App\Reservas\DTOs\Request\CrearReservaRequest;
use App\Reservas\DTOs\Response\PacienteReserva;
use App\Reservas\DTOs\Response\ProfesionalReserva;
use App\Reservas\DTOs\Response\RespuestaReserva;
use App\Reservas\Model\Reserva;
use App\Reservas\Model\ReservaConParticipantes;

class ReservaMapper {
    public static function toRequestCrear(array $input, int $idPaciente): CrearReservaRequest {
        return new CrearReservaRequest(
            $input["idProfesional"],
            $idPaciente,
            $input["fecha_reserva"],
        );
    }

    public static function toRequestActualizar(array $input): ActualizarReservaRequest {
        return new ActualizarReservaRequest(
            isset($input["fecha_reserva"]) ? $input["fecha_reserva"] : null,
            isset($input["estado"]) ? $input["estado"] : null
        );
    }

    public static function fromRequestCrear(CrearReservaRequest $request): Reserva {
        return Reserva::create(
            $request->getIdPaciente(),
            $request->getIdProfesional(),
            $request->getFechaReserva(),
            $request->getEstadoReserva()
        );
    }

    public static function fromRequestActualizar(Reserva $reserva, ActualizarReservaRequest $request): Reserva {
        if($request->getFechaReserva() !== null) {
            $reserva->setFechaReserva($request->getFechaReserva());
        }
        if($request->getEstadoReserva() !== null) {
            $reserva->setEstado($request->getEstadoReserva());
        }
        return $reserva;
    }

    public static function toResponse(ReservaConParticipantes $reserva): RespuestaReserva {
        return new RespuestaReserva(
            $reserva->id,
            new PacienteReserva(
                $reserva->idPaciente,
                $reserva->nombrePaciente,
                $reserva->apellidoPaciente
            ),
            new ProfesionalReserva(
                $reserva->idProfesional,
                $reserva->nombreProfesional,
                $reserva->apellidoProfesional,
                $reserva->profesion
            ),
            $reserva->fechaReserva,
            $reserva->estado
        );
    }
}