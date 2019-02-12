<?php

namespace App\Controller;

use App\Service\Functional\FetchRestaurantWithCurlService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use App\Service\Functional\UpdateRestaurantService;
use App\Entity\Restaurant;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class TodaysSpecialController extends Controller
{
    /**
     * @Route("/todays/special", name="todays_special")
     * @param UpdateRestaurantService $restaurantsService
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index(UpdateRestaurantService $restaurantsService, Request $request)
    {
        $locale = $request->getLocale();
        $intl = new \IntlDateFormatter($request->getLocale(), \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'd LLLL y');
        $date = $intl->format(new \DateTime('now'));
        $lastUpdateToDisplay = $restaurantsService->getLastUpdateToDisplay();

        $caseWeekEnd = FetchRestaurantWithCurlService::caseWeekend();
        if(@$_POST['refresh'] == "refreshed" && $caseWeekEnd == false){
            $toRefresh = true;
            $restaurantsService->updateAllRestaurants($caseWeekEnd, $toRefresh, $date);
        } else {
            $toRefresh = false;
            $restaurantsService->updateAllRestaurants($caseWeekEnd, $toRefresh, $date);
        }

        $restaurants = $this->getDoctrine()
            ->getRepository(Restaurant::class)
            ->findAll();
        ;

        $user = $this->getUser();

        $weekday = strtolower(FetchRestaurantWithCurlService::getDay());

        return $this->render('todays_special/index.html.twig', [
            'controller_name' => 'TodaysSpecialController',
            'restaurants' => $restaurants,
            'weekDay' => $weekday,
            'lastUpdateToDisplay' => $lastUpdateToDisplay,
            'user' => $user,
        ]);
    }







}
