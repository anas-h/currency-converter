<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\ConversionHistory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class HistoryController extends AbstractController
{
    #[Route('/history', name: 'app_history', methods: ['GET'])]
    public function index(ConversionHistory $history, #[CurrentUser] User $user): Response
    {
        return $this->render('history/index.html.twig', [
            'conversions' => $history->forUser($user, 50),
        ]);
    }
}
