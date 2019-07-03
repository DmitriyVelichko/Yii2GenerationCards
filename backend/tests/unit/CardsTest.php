<?php

namespace backend\tests;

use backend\test\fixtures\CardsFixture;

class CardsTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    public function _fixtures()
    {
        return ['cards' => CardsFixture::className()];
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {
        $this->example();
    }

    public function testGetName()
    {
        $card = $this->tester->grabFixture('cards','card1');
    }

    public function example()
    {

    }

}