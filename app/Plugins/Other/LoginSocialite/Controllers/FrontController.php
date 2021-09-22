<?php
#App\Plugins\Other\LoginSocialite\Controllers\FrontController.php
namespace App\Plugins\Other\LoginSocialite\Controllers;

use App\Plugins\Other\LoginSocialite\AppConfig;
use SCart\Core\Front\Models\ShopCustomer;
use SCart\Core\Front\Models\ShopCustomerAddress;
use App\Http\Controllers\RootFrontController;
use Laravel\Socialite\Facades\Socialite;

class FrontController extends RootFrontController
{
    public $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->plugin = new AppConfig;
    }

    /**
     * Redirect the user to the Social authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from Social.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        try {
            $getInfo  = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect(sc_route('login'));
        }
        $dataMap = [
            'email' => $getInfo->getEmail(),
            'provider' => $provider,
            'provider_id' => $getInfo->getId(),
            'first_name' => $getInfo->getName(),
            'last_name' => '',
            'postcode' => '',
            'address1' => $getInfo->location ?? '',
            'address2' => '',
            'country' => '',
            'phone' => '',
        ];
        $user = $this->createUser($dataMap, $provider);
        if(empty($user['error'])) {
            auth()->login($user);
            return redirect()->route('home')->with(['success' => sc_language_render('admin.login_success')]);
        } else {
            return redirect()->route('home')->with(['error' => $user['error']]);
        }

    }

    /**
     * Create customer when login provider
     *
     * @param   [array]  $dataMap
     * @param   [string]  $provider
     *
     * @return  [type]            [return description]
     */
    function createUser($dataMap, $provider)
    {
        $user = ShopCustomer::where(
            [
                ['provider_id', '=', $dataMap['provider_id']],
                ['provider', '=', $dataMap['provider']]
            ]
        )
            ->first();
        if (!$user) {
            $checkEmail =  ShopCustomer::where('email', $dataMap['email'])
                ->where('provider', '<>', $provider)->first();
            if(!$checkEmail) {
                $user = ShopCustomer::create([
                    'first_name'  => $dataMap['first_name'],
                    'email'       => $dataMap['email'],
                    'provider'    => $dataMap['provider'],
                    'provider_id' => $dataMap['provider_id'],
                    'address1'    => $dataMap['address1'],
                    'email_verified_at' => \ Carbon \ Carbon :: now (),
                ]);

                $dataAddress = [
                    'first_name' => $dataMap['first_name'],
                    'address1'   => $dataMap['address1'],
                ];
                $address = $user->addresses()->save(new ShopCustomerAddress($dataAddress));
                $user->address_id = $address->id;
                $user->save();
                return $user;


            } else {
                $user = [
                    'error' => sc_language_render($this->plugin->pathPlugin.'::lang.email_exist')
                ];
            }
        }
        return $user;
    }
}
