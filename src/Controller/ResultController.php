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