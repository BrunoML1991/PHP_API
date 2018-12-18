<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 14/12/2018
 * Time: 18:38
 */

namespace App\Controller;

use App\Entity\Result;
use App\Entity\User;
use App\Errors;
use App\JWTManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class ResultController
 * @package App\Controller
 * @Route(path=ResultController::RESULT_API_PATH, name="api_results_")
 */
class ResultController extends AbstractController
{
    const RESULT_API_PATH = '/api/v1/results';

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route(path="",name="getc",methods={Request::METHOD_GET})
     */
    public function getCResult(Request $request):JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(Result::class)->findAll();
        return ($results !== null)
            ? new JsonResponse(['results' => $results])
            : $error->error404();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @Route(path="",name="post",methods={Request::METHOD_POST})
     */
    public function postResult(Request $request):JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        if (!array_key_exists('result', $datos) || !array_key_exists('user_id', $datos) ) {
            return $error->error422();
        }
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($datos['user_id']);
        if ($user === null) {
            return $error->error404();
        }
        $result = new Result(
            $datos['result'],
            $user,
            new \DateTime('now')
        );
        $em->persist($result);
        $em->flush();
        return new JsonResponse(['result'=>$result],Response::HTTP_CREATED);
    }

    /**
     * @return JsonResponse
     * @Route(path="",name="options",methods={Request::METHOD_OPTIONS})
     */
    public function optionsResult():JsonResponse
    {
        return new JsonResponse(
            'Allow header',
            Response::HTTP_OK,
            ['allow' => Request::METHOD_GET . ', ' . Request::METHOD_POST]
        );
    }

    private function authorization(Request $request)
    {
        /** @var Errors $error */
        $error = new Errors();
        $jwtManager = new JWTManager();
        $token = $request->headers->get('token');
        if ($token === null || !$jwtManager->isValidToken($token)) {
            return $error->error401();
        }
        if (!$jwtManager->isAdminUser($token)) {
            return $error->error403();
        }
        return true;
    }
}