<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 06/02/19
 * Time: 15:45
 */

namespace App\Service\Functional;

use App\Entity\LastUpdate;
use App\Entity\Restaurant;
use App\Repository\LastUpdateRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityNotFoundException;

class UpdateRestaurantService
{
    CONST DAYS = [
        1 => "Lundi",
        2 => "Mardi",
        3 => "Mercredi",
        4 => "Jeudi",
        5 => "Vendredi",
        6 => "Samedi",
        7 => "Dimanche"
    ];

    /**
     * @var RestaurantRepository
     */
    protected $restaurantRepository;

    /**
     * @var LastUpdateRepository
     */
    protected $lastUpdateRepository;

    public function __construct(RestaurantRepository $restaurantRepository, LastUpdateRepository $lastUpdateRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->lastUpdateRepository = $lastUpdateRepository;
    }

    /**
     * @return void
     * @throws EntityNotFoundException if some restaurant were not found
     */
    public function updateAllRestaurants($caseWeekEnd, $toRefresh = false, $date)
    {
        //Important : these names and methods in CurlRestaurantsService must be the same (and without spaces!)
        $restaurantTab = [
            'Le K',
            'Marche Biot',
            'Les Hirondelles',
            'La Petite Pause',
            'Air Bagel',
            'Papa Ciccio',
            'La Cave Profonde',
            'Chez Pipio'
        ];

        foreach($restaurantTab as $restaurantFromTab) {

            if(!$caseWeekEnd){
                unset($restaurant);
                $restaurant = $this->restaurantRepository->findOneByName($restaurantFromTab);
                if (!$restaurant) {
                    $restaurant = new Restaurant();
                    $restaurantFromTab = str_replace(' ','', $restaurantFromTab);
                    $functionName = "getCurlMenu" . $restaurantFromTab;

                    //            $name, [0]
                    //            $cleanMenu), // [1]
                    //            $url, // [2]
                    //            $mappy, // [3]
                    //            $price, // [4]
                    $restaurant->setName(FetchRestaurantWithCurlService::$functionName()[0]);
                    $restaurant->setTodaySpecial(FetchRestaurantWithCurlService::$functionName()[1]);
                    $restaurant->setUrl(FetchRestaurantWithCurlService::$functionName()[2]);
                    $restaurant->setMappy(FetchRestaurantWithCurlService::$functionName()[3]);
                    $restaurant->setPrice(FetchRestaurantWithCurlService::$functionName()[4]);
                    $restaurant->setPriority(0);
                    $restaurant->setLastUpdate(New \DateTime());

                    $this->restaurantRepository->save($restaurant);
                }

                $lastGlobalUpdate = $this->lastUpdateRepository->findAll();
                $firstDate = $lastGlobalUpdate[0]->getLastGlobalRefresh()->format('Y-m-d');
                $secondDate = new \DateTime;
                $secondDate = $secondDate->format('Y-m-d');

                if ((!($firstDate  == $secondDate)) || $toRefresh) {
                    $lastGlobalUpdate[0]->setLastGlobalRefresh(New \DateTime());

                    switch ($restaurantFromTab) {
                        case 'Chez Pipio' :
                            $restaurant->setLastUpdate(New \DateTime());
                            $restaurant->setTodaySpecial(FetchRestaurantWithCurlService::getCurlMenuChezPipio()[0]);
                        case 'Le K' :
                            $restaurant->setLastUpdate(New \DateTime());
                            $restaurant->setTodaySpecial(FetchRestaurantWithCurlService::getCurlMenuLeK());
                            break;
                        case 'Les Hirondelles':
                            $restaurant->setLastUpdate(New \DateTime());
                            $restaurant->setTodaySpecial(FetchRestaurantWithCurlService::getCurlMenuLesHirondelles()[0]);
                            break;
                        case 'La Petite Pause':
                            $restaurant->setLastUpdate(New \DateTime());
                            $restaurant->setTodaySpecial(FetchRestaurantWithCurlService::getCurlMenuLaPetitePause($date)[0]);
                            $restaurant->setPrice(FetchRestaurantWithCurlService::getCurlMenuLaPetitePausePrice($date));
                            break;
                        case 'Marche Biot':
                            $restaurant->setLastUpdate(New \DateTime());
                            $restaurant->setTodaySpecial(FetchRestaurantWithCurlService::getCurlMenuMarcheBiot()[0]);
                            $restaurant->setPrice('8,30 €');
                            break;
                        case 'Air Bagel' :
                            $restaurant->setTodaySpecial("
                                Salade ou Bagel.
                                Plus de détails sur le site...
                                ");
                            $restaurant->setLastUpdate(New \DateTime());
                            break;
                        case 'Papa Ciccio' :
                            $restaurant->setLastUpdate(New \DateTime());
                            break;
                        case 'La Cave Profonde':
                            $restaurant->setLastUpdate(New \DateTime());
                            $restaurant->setTodaySpecial(FetchRestaurantWithCurlService::getCurlMenuLaCaveProfonde()[1]);
                            break;
                    }
                    $this->restaurantRepository->save($restaurant);
                }
            } else {
                $lastGlobalUpdate = $this->lastUpdateRepository->findAll();
                $lastGlobalUpdate[0]->setLastGlobalRefresh(New \DateTime());

                $restaurant = $this->restaurantRepository->findOneByName($restaurantFromTab);
                $restaurant->setTodaySpecial("Pas de plat du jour le week-end ! Revenez nous voir lundi !");
                $this->restaurantRepository->save($restaurant);
            }
        }
    }

    /**
     * @return LastUpdateRepository
     */
    public function getLastUpdateToDisplay()
    {
        $lastGlobalUpdate = $this->lastUpdateRepository->findAll();


        $lastUpdate = $lastGlobalUpdate[0]->getLastGlobalRefresh();
        $now = new \DateTime;
        $nowCalendar = $now->format('Y-m-d');

        $interval = $lastUpdate->diff($now);

        //        //Returns "Aujourd'hui"
        if ($lastUpdate->format('Y-m-d') == $nowCalendar) {
            return "aujourd'hui à " . $lastUpdate->format('H:i');;
        }

        //Returns "Hier"
        if ($interval->format('%a') == 1){
            return "Hier";
        }

        //Returns "$day dernier"
        if ($interval->format('%a') <= 7){
            $dayInLetters = $lastUpdate->format('N');
            return self::DAYS[$dayInLetters] . " dernier";
        }

        //Returns "la semaine dernière"
        if ($interval->format('%a') > 7){
            return "Il y a plus d'une semaine";
        }


    }
}