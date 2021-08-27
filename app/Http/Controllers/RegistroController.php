<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registro;

use App\Mail\EnvioMail;
use Illuminate\Support\Facades\Mail;
use Validator;

class RegistroController extends ApiController
{

    public function eventPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'csvFile' => 'required|mimes:csv,xls,xlsx|max:15360',
            'event' => 'required'

        ]);
        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }
            switch ($request->event) {
                case 'deposito':
                    return $this->deposito($request);
                    break;
                case 'crear':
                    return $this->crear($request);;
                    break;
                case 'retiro':
                    return $this->retiro($request);
                    break;
                case 'transferencia':
                    return $this->transferencia($request);
                    break;
                case 'reset':
                    return $this->reset($request);
                    break;
            }
    }
    public function deposito(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'csvFile' => 'required|mimes:csv,xls,xlsx|max:15360',
            'destino' => 'required',
            'monto' => 'required'

        ]);
        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }
        $Registro = Registro::find($request->destino);
        if ($Registro) {
            $Registro->id = $request->input('destino');
            $Registro->balance = $Registro->balance + $request->monto;
            $Registro->save();
            return $this->sendResponse($Registro, "El deposito se a hecho corectamente ", 200);
        } else {
            return $this->sendError("Error Conocido", "Error. La cuenta solicitada no existe", 404);
        }
    }

    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'csvFile' => 'required|mimes:csv,xls,xlsx|max:15360',
            'id' => 'required',
            'mail' => 'required|email'
        ]);
        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }
        $Registro2 = Registro::where('email',$request->input('mail'))
            ->select('id', 'balance', 'email')
            ->get();
        if ($Registro2 === null) {
            $Registro = Registro::find($request->id);
            if (!$Registro) {
                $Registro = new Registro();
                $Registro->id = $request->input('id');
                $Registro->email = $request->input('mail');
                $Registro->balance = 0;
                $Registro->save();
                return $this->sendResponse($Registro, "Registro creado corectamente", 201);
            } else {
                return $this->sendError("Error Conocido", "Error. La cuenta solicitada ya existe", 404);
            }
        }else {
            return $this->sendError("Error Conocido", "Errorwefisnfijwebfuiwehfbiewbfwefb", 404);
        } 
    }

    public function balance($id)
    {
        $Registro = Registro::where('id', $id)
            ->select('id', 'balance', 'email')
            ->get();
        if (strlen($Registro) > 2) {
            return $this->sendResponse($Registro, "Registro obtenido correctamente");
        } else {
            return $this->sendError("Error Conocido", "Error al buscar el registro", 404);
        }
    }

    public function retiro(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //'csvFile' => 'required|mimes:csv,xls,xlsx|max:15360',
            'origen' => 'required',
            'monto' => 'required'

        ]);
        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }
        $retiro = $request->monto;
        $Registro = Registro::find($request->origen);
        switch ($retiro) {
            case $retiro > $Registro->balance:
                return $this->sendError("Error mensje", "El retiro supera el monto del usuario", 404);
                break;
            case $retiro >= 1000:
                return $this->token1($Registro);
                // return $this->sendError("Error mensje", "El retiro supera los 1000$ es necesario un token", 404);
                break;
            default;
                $Registro->balance = $Registro->balance - $retiro;
                $Registro->save();
                return $this->sendResponse($Registro, "Retiro realizado corectamente");
                break;
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
                    $this->token1($request);
                } else {
                    return $this->sendError("Error Conocido", [$idOrigen, $idDestino, $montoR], 404);
                }
            }
        } else {
            return $this->sendError("Error Conocido", "Error: No se pudo encontrar la cuenta de horigen o destino", 404);
        }
    }

    public function reset()
    {
        Registro::truncate();
        return $this->sendResponse("!!!!!!!", "Todos los registros an sido borados", 200);
    }

    public function token1($Registro)
    {
        $data = array();
        $data['token'] = random_int(1000, 5000);
        $data['email'] = $Registro->email;
        $data['idUser'] = $Registro->origen;
        Mail::send('emails.mailCodigo', $data, function ($msj) use ($data) {
            $msj->subject('Envio de TOKEN');
            $msj->to($data['email']);
        });
        return $this->sendResponse("Token enviado", "revise su mail", 200);
    }
}
