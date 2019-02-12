<?php

namespace App\Command;

use App\Repository\JobRepository;
use App\Service\Functional\UpdateRestaurantService;
use App\Service\JobService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends BaseCronCommand
{
    protected static $defaultName = 'app:update';

    /**
     * @var UpdateRestaurantService $updateRestaurant
     */
    protected $updateRestaurant;

    public function __construct(
        string $name = null,
        JobRepository $jobRepository,
        LoggerInterface $logger,
        JobService $jobService,
        UpdateRestaurantService $updateRestaurant
    ) {
        parent::__construct($name, $jobRepository, $jobService, $logger);
        $this->updateRestaurant = $updateRestaurant;
    }

    protected function configure()
    {
        $this
            // every day at 05:00 AM
            ->setDateTimePattern('*-*-* 11:00')
            ->setName(self::$defaultName)
            ->setDescription('Update periodicaly the restaurants menus')
            ->setHelp('This command update Restaurant\'smenus list');
    }

    function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dateTime = new \DateTime();
        $date = $dateTime->format('Y-m-d');
        $this->updateRestaurant->updateAllRestaurants($this->isWeekend($date), true, $date);
    }
}
