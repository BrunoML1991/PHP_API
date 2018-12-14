<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 14/12/2018
 * Time: 18:38
 */

namespace App\Controller;


use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResultController
 * @package App\Controller
 * @Route(path=ResultController::RESULT_API_PATH, name="api_results_")
 */
class ResultController
{
    const RESULT_API_PATH = '/api/v1/results';

}