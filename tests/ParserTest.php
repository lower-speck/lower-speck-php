<?php

namespace LowerSpeck;

use TestCase;
use Mockery;

class ParserTest extends TestCase
{
    public function setUp()
    {
        if (file_exists($this->base_path('x.lwr'))) {
            unlink($this->base_path('x.lwr'));
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->base_path('x.lwr'))) {
            unlink($this->base_path('x.lwr'));
        }
    }

    public function testInstantiate()
    {
        new Parser('');
    }

    /**
     * @LWR 1.d. The class MUST parse the `requirements.lwr` file into an 
     * appropriate structure.
     */
    public function testGetSpecification()
    {
        file_put_contents($this->base_path('x.lwr'),
              "1. Something MUST do some action.\n"
            . "\n"
            . "1.a. (X, I) Something MAY do some other action.\n"
        );

        $parser = new Parser($this->base_path('x.lwr'));

        $expected_data = [
            new Requirement('1. Something MUST do some action.'),
            '',
            new Requirement('1.a. (X, I) Something MAY do some other action.'),
            '',
        ];
        
        $specification = $parser->getSpecification();
        
        $this->assertEquals($expected_data, $specification->getAll());
    }

}
