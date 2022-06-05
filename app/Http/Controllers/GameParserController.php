<?php

namespace App\Http\Controllers;

use AmrShawky\LaravelCurrency\Facade\Currency;
use Goutte\Client;

class GameParserController extends Controller {
    private function get_link( $game, $region ): string {
        return 'https://www.xbox.com/' . $region . '/games/store/' . $game;
    }

    private function get_games(): array {
        return [
            'grand-theft-auto-v-premium-edition/c496clvxmjp8',
            'red-dead-redemption-2/9n2zdn7nwqkv',
            'fifa-22-xbox-one/9n9j38lpvsm3',
            'mortal-kombat-11/btc0l0bw6lwc',
            'elden-ring/9p3j32ctxlrz',
        ];
    }


    private function get_regions(): array {
        return [
            [
                'url_locale' => 'en-us',
                'currency'   => 'USD',
            ],
            [
                'url_locale' => 'es-AR',
                'currency'   => 'ARS',
            ],
            [
                'url_locale' => 'tr-TR',
                'currency'   => 'TRY',
            ],
        ];
    }

    private function get_converted_price( $price, $currency_from ): string {
        return '₴' . Currency::convert()->from( $currency_from )->to( 'UAH' )->amount( $this->get_clear_price( $price ) )->round( 0 )->get();
    }

    private function get_clear_price( $money ): float {
        $clean_string        = preg_replace( '/([^0-9\.,])/i', '', $money );
        $only_numbers_string = preg_replace( '/([^0-9])/i', '', $money );

        $separators_count_to_be_erased = strlen( $clean_string ) - strlen( $only_numbers_string ) - 1;

        $string_with_comma_or_dot   = preg_replace( '/([,\.])/', '', $clean_string,
            $separators_count_to_be_erased );
        $removed_thousand_separator = preg_replace( '/(\.|,)(?=[0-9]{3,}$)/', '', $string_with_comma_or_dot );

        return (float) str_replace( ',', '.', $removed_thousand_separator );
    }

    public function run() {
        $client  = new Client();
        $games   = $this->get_games();
        $regions = $this->get_regions();
        $data    = [];

        foreach ( $regions as $region ) {
            foreach ( $games as $game ) {
                $response = $client->request( 'GET', $this->get_link( $game, $region['url_locale'] ) );

                if ( 'en-us' === $region['url_locale'] ) {
                    $data[ $game ] = [
                        'title'     => $response->filter( '.typography-module__xdsH1___zrXla' )->text(),
                        'image_url' => $response->filter( '.ProductDetailsHeader-module__productImage___tT14m' )->attr( 'src' ),
                    ];
                }

                $price = $response->filter( '.Price-module__boldText___34T2w' )->text();

                $data[ $game ]['prices'][ $region['url_locale'] ] = [
                    'original'  => $price,
                    'converted' => $this->get_converted_price( $price, $region['currency'] ),
                ];
            }
        }

        dd( $data );
    }
}

