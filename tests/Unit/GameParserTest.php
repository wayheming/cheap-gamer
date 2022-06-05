<?php

namespace Tests\Unit;

use AmrShawky\LaravelCurrency\Facade\Currency;
use App\Http\Controllers\GameParserController;
use Tests\TestCase;
use ReflectionClass;

class GameParserTest extends TestCase {
    private $object;
    private $reflection;

    protected function setUp(): void {
        parent::setUp();

        $this->object     = new GameParserController();
        $this->reflection = new ReflectionClass( $this->object );
    }

    public function test_clean_price() {
        $method = $this->reflection->getMethod( 'get_clear_price' );
        $method->setAccessible( true );
        $result = $method->invokeArgs( $this->object, [ '$14.99+' ] );

        $this->assertEquals( '14.99', $result );
    }

    public function test_converted_price() {
        $method = $this->reflection->getMethod( 'get_converted_price' );
        $method->setAccessible( true );
        $result = $method->invokeArgs( $this->object, [ '$14.99+', 'USD' ] );

        $expect = '₴' . Currency::convert()->from( 'USD' )->to( 'UAH' )->amount( '14.99' )->round( 0 )->get();

        $this->assertEquals( $expect, $result );
    }
}
