<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 14/12/2018
 * Time: 19:35
 */

namespace App;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Errors
{

    public function error400(): JsonResponse
    {
        $mensaje = [
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => 'Bad Request: User name or e-mail already exists'
        ];
        return new JsonResponse(
            $mensaje,
            Response::HTTP_BAD_REQUEST
        );
    }

    public function error404(): JsonResponse
    {
        $mensaje = [
            'code' => Response::HTTP_NOT_FOUND,
            'message' => 'Not Found'
        ];
        return new JsonResponse(
            $mensaje,
            Response::HTTP_NOT_FOUND
        );
    }

    public function error422(): JsonResponse
    {
        $mensaje = [
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Unprocesable Entity'
        ];
        return new JsonResponse(
            $mensaje,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

}