<?php

namespace App\Controller;

use App\Repository\FrameRepository;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(Request $request, ChartBuilderInterface $chartBuilder, FrameRepository $frameRepository): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $date = (new DateTimeImmutable('tomorrow'))->modify('last monday');
        $dateString = $request->get('date');

        if (null !== $dateString) {
            $date = new DateTimeImmutable($dateString);
            $date = $date->modify('tomorrow')->modify('last monday');
        }

        $endDate = $date->modify('next Sunday');

        $days = [];
        $frames = $frameRepository->findByStartAndEnd($user, $date, $endDate);
        foreach ($frames as $frame) {
            $weekday = $frame->getStartAt()->format('w') - 1;
            if (-1 === $weekday) {
                $weekday = 7;
            }
            $days[$weekday]['date'] = $frame->getStartAt('d-m-Y');
            $days[$weekday]['frames'][] = $frame;
        }

        $calculated = [];
        for ($i = 0; $i < 6; ++$i) {
            $calculated[$i] = 0;
        }
        foreach ($days as $weekday => $frames) {
            $calculated[$weekday] = 0;
            foreach ($frames['frames'] as $frame) {
                $calculated[$weekday] = $calculated[$weekday] + (($frame->getEndAt()->getTimestamp() - $frame->getStartAt()->getTimestamp()) / 3600);
            }
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'datasets' => [
                ['type' => 'bar', 'label' => 'Worked Time', 'data' => $calculated],
                ['type' => 'line', 'label' => 'Time', 'data' => [8.4, 8.4, 8.4, 8.4, 8.4], 'borderColor' => 'rgb(255, 99, 132)', 'backgroundColor' => 'rgb(255, 99, 132)'],
            ],
        ]);

        return $this->render('index/profile.html.twig', [
            'chart' => $chart,
            'frames' => $frames,
            'date' => $date->format('c'),
            'days' => $days,
        ]);
    }

    #[Route('/user-events', name: 'app_user_events')]
    #[IsGranted('ROLE_USER')]
    public function userEvents(Request $request, FrameRepository $frameRepository): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $dateString = $request->get('date');
        $date = new DateTimeImmutable($dateString);

        $endDate = $date->modify('next Sunday');

        $frames = $frameRepository->findByStartAndEnd($user, $date, $endDate);

        $return = [];
        foreach ($frames as $frame) {
            $return[] = [
                'id' => $frame->getId(),
                'title' => $frame->getProject()->getName(),
                'start' => $frame->getStartAt()->format('c'),
                'end' => $frame->getEndAt()->format('c'),
            ];
        }

        return $this->json($return);
    }
}
