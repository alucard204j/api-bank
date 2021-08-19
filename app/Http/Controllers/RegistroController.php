<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registro;

use App\Mail\EnvioMail;
use Illuminate\Support\Facades\Mail;

class RegistroController extends ApiController
{
    public function deposito(Request $request)
    {
        $Registro = Registro::find($request->destino);
        if (strlen($Registro) > 2) {
            $Registro->id = $request->input('destino');
            $Registro->balance = $Registro->balance + $request->input('monto');
            $Registro->save();
            return $this->sendResponse($Registro, "El deposito se a hecho corectamente ", 200);
        } else {
            return $this->sendError("Error Conocido", "Error. La cuenta solicitada no existe", 404);
        }
    }

    public function crear($id)
    {
        $Registro = Registro::find($id);
        if (strlen($Registro) < 2) {
            $Registro = new Registro();
            $Registro->id = $id;
            $Registro->balance = 0;
            $Registro->save();
            return $this->sendResponse($id, "Registro creado corectamente", 201);
        } else {
            return $this->sendError("Error Conocido", "Error. La cuenta solicitada ya existe", 404);
        }
    }

    public function balance($id)
    {
        $Registro = Registro::where('id', $id)
            ->select('id', 'balance')
            ->get();
        if (strlen($Registro) > 2) {
            return $this->sendResponse($Registro, "Registro obtenido correctamente");
        } else {
            return $this->sendError("Error Conocido", "Error al buscar el registro", 404);
        }
    }

    public function retiro(Request $request)
    {
        $reqID = $request->origen;
        $retiro = $request->monto;
        $Registro = Registro::find($reqID);
        if ($Registro->balance > $retiro) {
            $Registro->balance = $Registro->balance - $retiro;
            $Registro->save();
            return $this->sendResponse($Registro, "Retiro realizado corectamente");
        } else {
            return $this->sendError("Error Conocido", "Error: el retiro supera el balnce", 404);
        }
    }

    public function transferencia(Request $request)
    {
        $idOrigen = $request->origen;
        $idDestino = $request->destino;
        $montoR = $request->monto;
        $RegistroOtrigrn = Registro::find($idOrigen);
        $RegistroDestno = Registro::find($idDestino);
        if (strlen($RegistroOtrigrn) > 2 && strlen($RegistroDestno) > 2) {
            if ($montoR < 1000 && $montoR < $RegistroOtrigrn->balance) {

                $RegistroOtrigrn->balance = $RegistroOtrigrn->balance - $montoR;
                $RegistroDestno->balance = $RegistroDestno->balance + $montoR;
                $RegistroOtrigrn->save();
                $RegistroDestno->save();

                return $this->sendResponse([$RegistroOtrigrn, $RegistroDestno], "Transferencia realizado corectamente");
            } else {
                if ($montoR >= 1000) {
                    $correo = new EnvioMail;
                    Mail::to('jonathan.cembranos@anima.edu.uy')->send($correo);
                    return $this->sendError("Error Conocido", "se envio un mail con su codigo de verificacion", 404);
                } else {
                    return $this->sendError("Error Conocido", [$idOrigen, $idDestino, $montoR], 404);
                }
            }
        } else {
            return $this->sendError("Error Conocido", "Error: No se pudo encontrar la cuenta de horigen o destino", 404);
        }
    }

    public function mail2()
    {
        $correo = new EnvioMail;
        Mail::to('jonathan.cembranos@anima.edu.uy')->send($correo);
        return $this->sendError("Error Conocido", "se envio un mail con su codigo de verificacion", 404);
    }
}
