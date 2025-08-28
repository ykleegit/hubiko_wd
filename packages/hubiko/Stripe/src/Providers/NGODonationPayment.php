<?php

namespace Hubiko\Stripe\Providers;

use App\Models\WorkSpace;
use Illuminate\Support\ServiceProvider;

class NGODonationPayment extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot(){

        view()->composer(['ngomanagment::front_end.pages.donate-now' , 'ngomanagment::front_end.index'], function ($view)
        {
                $slug = \Request::segment(1);
           
                $workspace = WorkSpace::where('slug',$slug)->first();
                $company_settings = getCompanyAllSetting($workspace->created_by,$workspace->id);
                if((isset($company_settings['stripe_is_on']) ? $company_settings['stripe_is_on'] : 'off') == 'on' && !empty($company_settings['stripe_key']) && !empty($company_settings['stripe_secret']))
                {
                    $view->getFactory()->startPush('ngo_donation_payment', view('stripe::payment.ngo_donation_payment', compact('slug')));
                }
        });
    }

    public function register()
    {

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}