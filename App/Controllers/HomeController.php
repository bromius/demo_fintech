<?php
namespace App\Controllers;

/**
 * Handles the application's main page
 */
class HomeController extends Controller
{
    /**
     * Displays the application's home page
     * 
     * @return void Renders the home view template
     */
    public function index()
    {
        $this->render('home.php', [
            'systemCurrency' => cfg('app.system_currency')
        ]);
    }
}