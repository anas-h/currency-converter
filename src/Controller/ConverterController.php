<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Exception\RateProviderUnavailableException;
use App\Exception\UnknownCurrencyException;
use App\Form\ConversionType;
use App\Model\ConversionRequest;
use App\Service\ConversionHistory;
use App\Service\CurrencyConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ConverterController extends AbstractController
{
    #[Route('/', name: 'app_converter', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        CurrencyConverter $converter,
        ConversionHistory $history,
        #[CurrentUser] User $user,
    ): Response {
        $conversionRequest = new ConversionRequest();
        $form = $this->createForm(ConversionType::class, $conversionRequest);
        $form->handleRequest($request);

        $result = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $result = $converter->convert(
                    $conversionRequest->normalizedAmount(),
                    (string) $conversionRequest->from,
                    (string) $conversionRequest->to,
                );
                $history->record($user, $result);
            } catch (UnknownCurrencyException|\InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));
            } catch (RateProviderUnavailableException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('converter/index.html.twig', [
            'form' => $form,
            'result' => $result,
            'recent' => $history->forUser($user, 5),
        ], new Response(status: $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}
