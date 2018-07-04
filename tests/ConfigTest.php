<?php

namespace LowerSpeck;

use TestCase;
use Mockery;

class ConfigTest extends TestCase
{
    public function setUp()
    {
        if (file_exists($this->base_path('x.json'))) {
            unlink($this->base_path('x.json'));
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->base_path('x.json'))) {
            unlink($this->base_path('x.json'));
        }
    }

    public function testInstantiate()
    {
        new Config($this->str_random(9));
    }

    /**
     * @LWR 1.b.a. The class SHOULD expect the config file to contain an object 
     * in JSON format.
     * 
     * @LWR 1.b.b. The class SHOULD expect the object's key named `paths` to 
     * have an array of strings.
     * 
     * @LWR 1.c. The class MUST use default values if the `lower-speck.json` 
     * file is absent.
     */
    public function testPaths()
    {
        $config = new Config($this->base_path('x.json'));
        $this->assertEquals(['.'], $config->paths());

        file_put_contents($this->base_path('x.json'), '');
        $config = new Config($this->base_path('x.json'));
        $this->assertEquals(['.'], $config->paths());

        file_put_contents($this->base_path('x.json'), json_encode([]));
        $config = new Config($this->base_path('x.json'));
        $this->assertEquals(['.'], $config->paths());

        file_put_contents($this->base_path('x.json'), json_encode([
            'paths' => [],
        ]));
        $config = new Config($this->base_path('x.json'));
        $this->assertEquals(['.'], $config->paths());

        $path = $this->str_random(8);
        file_put_contents($this->base_path('x.json'), json_encode([
            'paths' => [$path],
        ]));
        $config = new Config($this->base_path('x.json'));
        $this->assertEquals([$path], $config->paths());

        $paths = [$this->str_random(8), $this->str_random(8)];
        file_put_contents($this->base_path('x.json'), json_encode([
            'paths' => $paths,
        ]));
        $config = new Config($this->base_path('x.json'));
        $this->assertEquals($paths, $config->paths());
    }
}
