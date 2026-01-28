<?php
namespace App\Helper;

use DateTime;

class GeneradorIcs {
    public function generarIcs(
        string $fechaInicio,
        string $fechaFin,
        string $titulo,
        string $descripcion
    ) {
        $formato = "Ymd:THis";

        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $ahora = new DateTime();

        $uid = md5($titulo . $fechaInicio) . "@sistemareservas.com";

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Sistema de Reservas//ES\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:$uid\r\n";
        $ics .= "DTSTAMP:" . $ahora->format($formato) . "\r\n";
        $ics .= "DTSTART:" . $inicio->format($formato) . "\r\n";
        $ics .= "DTEND:" . $fin->format($formato) . "\r\n";
        $ics .= "SUMMARY:$titulo\n";
        $ics .= "DESCRIPTION:$descripcion.\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR";

        return $ics;
    }
}