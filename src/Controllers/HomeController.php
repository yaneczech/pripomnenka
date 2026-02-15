<?php
/**
 * Připomněnka - HomeController
 */

declare(strict_types=1);

namespace Controllers;

class HomeController extends BaseController
{
    /**
     * Úvodní stránka
     */
    public function index(array $params): void
    {
        // Pokud je přihlášen, přesměrovat na připomínky
        if (\Session::isLoggedIn()) {
            $this->redirect('/moje-pripominky');
        }

        $planModel = new \Models\SubscriptionPlan();
        $plans = $planModel->getForLandingPage();

        $this->view('home/index', [
            'title' => 'Připomněnka',
            'plans' => $plans,
        ], 'public');
    }
}
