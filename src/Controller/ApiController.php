<?php

namespace App\Controller;

use App\Entity\Frame;
use App\Entity\Project;
use App\Repository\FrameRepository;
use App\Repository\ProjectRepository;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/watson', name: 'app_api_')]
class ApiController extends AbstractController
{
    #[Route('/projects', name: 'projects')]
    public function projects(ProjectRepository $projectRepository): Response
    {
        $user = $this->getUser();
        $projects = $projectRepository->findBy(['createdBy' => $user]);

        $return = [];
        foreach ($projects as $project) {
            $return[] = [
                'id' => $project->getId()->toRfc4122(),
                'name' => $project->getName(),
            ];
        }

        return $this->json($return);
    }

    #[Route('/frames', name: 'frames')]
    public function frames(ProjectRepository $projectRepository, Request $request): Response
    {
        $lastSync = new DateTimeImmutable($request->get('last_sync'));
        $user = $this->getUser();
        $projects = $projectRepository->findBy(['createdBy' => $user]);

        $return = [];
        foreach ($projects as $project) {
            foreach ($project->getFrames() as $frame) {
                if (null !== $lastSync && $frame->getUpdatedAt() < $lastSync) {
                    continue;
                }
                $return[] = [
                    'id' => $frame->getId()->toRfc4122(),
                    'begin_at' => $frame->getStartAt()->format('c'),
                    'end_at' => $frame->getEndAt()->format('c'),
                    'project' => $project->getName(),
                    'tags' => $frame->getTags(),
                ];
            }
        }

        return $this->json($return);
    }

    #[Route('/frames/bulk/', name: 'frames_bulk', methods: ['POST'])]
    public function bulkFrames(ProjectRepository $projectRepository, FrameRepository $frameRepository, Request $request): Response
    {
        $user = $this->getUser();

        $frames = $request->toArray();

        foreach ($frames as $frameArray) {
            $project = $projectRepository->findOneBy(['name' => $frameArray['project']]);
            if (null === $project) {
                $project = new Project();
                $project->setName($frameArray['project'])
                    ->setCreatedBy($user);

                $projectRepository->add($project, true);
            }

            $loc = new DateTimeImmutable();
            $beginAtUtc = new DateTimeImmutable($frameArray['begin_at'], new DateTimeZone('UTC'));
            $endAtUtc = new DateTimeImmutable($frameArray['end_at'], new DateTimeZone('UTC'));

            $beginAt = $beginAtUtc->setTimezone($loc->getTimezone());
            $endAt = $endAtUtc->setTimezone($loc->getTimezone());

            $uuid = Uuid::fromString(substr($frameArray['id'], strlen('urn:uuid:')));
            $frame = $frameRepository->find($uuid);
            if (null === $frame) {
                $frame = new Frame();
            }
            $frame
                ->setId($uuid)
                ->setProject($project)
                ->setStartAt($beginAt)
                ->setUpdatedAt(new DateTimeImmutable())
                ->setEndAt($endAt)
                ->setTags($frameArray['tags']);

            $frameRepository->add($frame, true);
        }

        return new Response('', 201);
    }
}
