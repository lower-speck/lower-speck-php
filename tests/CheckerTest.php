<?php

namespace LowerSpeck;

use TestCase;
use Mockery;

class CheckerTest extends TestCase
{

    /**
     * @LWR 1.e.a. The class constructor MUST accept a base path as its first 
     * parameter.
     */
    public function testInstantiate()
    {
        new Checker($this->base_path());
    }

    /**
     * @LWR 1.e.a. The class constructor MUST accept a base path as its first 
     * parameter.
     * 
     * @LWR 1.a. The class MUST expect the Lower Speck file to be named 
     * `requirements.lwr` and reside at the base path of the project.
     * 
     * @LWR 1.b. The class SHOULD expect a config file named `lower-speck.json` 
     * to reside at the base path of the project.
     * 
     * @LWR 1.d. The class MUST parse the `requirements.lwr` file into an 
     * appropriate structure.
     * 
     * @LWR 1.f. The class MUST grep the directories from the `paths` array (or 
     * else the root) recursively to find strings referencing the requirements 
     * from the `requirements.lwr` file.
     * 
     * @LWR 1.g. The class MUST return an analysis of the requirements and 
     * state of code.
     *
     * @LWR 2.b.a. The script MUST accept verbosity flags `-v` and `-vv`.
     *
     * @LWR 2.b.b. In normal mode and above the script MUST output any 
     * requirements that are not addressed and not obsolete as well as any 
     * incomplete requirements that are addressed and not obsolete.
     * 
     * @LWR 2.b.c. In verbose mode and above the script MUST output all 
     * requirements that are not obsolete.
     * 
     * @LWR 2.b.d. In double-verbose mode and above the script MUST output all 
     * requirements.
     *
     * @LWR 1.e.b. The class MUST accept an ID as an optional parameter to its 
     * checking method.
     */
    public function testCheck()
    {
        $caught_requirements_file = null;
        $caught_config_file = null;
        $caught_paths_list = null;
        $caught_specification = null;
        $caught_grepper = null;

        $id = 2;
        $spec = Mockery::mock(Specification::class);
        $grepper = Mockery::mock(ReferenceGrepper::class);
        $analysis = Mockery::mock(Analysis::class);

        Make::bind(Parser::class, function ($filepath) use (&$caught_requirements_file, $spec) {
            $caught_requirements_file = $filepath;
            $parser = Mockery::mock(Parser::class);
            $parser->shouldReceive('getSpecification')
                ->once()
                ->andReturn($spec);
            return $parser;
        });
        
        Make::bind(Config::class, function ($filepath) use (&$caught_config_file) {
            $caught_config_file = $filepath;
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('paths')
                ->andReturn(['x', 'y', 'z']);
            return $config;
        });
        
        Make::bind(ReferenceGrepper::class, function ($paths) use (&$caught_paths_list, $grepper) {
            $caught_paths_list = $paths;
            return $grepper;
        });

        Make::bind(Analyzer::class, function ($specification, $grepper) use (&$caught_specification, &$caught_grepper, $analysis, $id) {
            $caught_specification = $specification;
            $caught_grepper = $grepper;
            $analyzer = Mockery::mock(Analyzer::class);
            $analyzer->shouldReceive('getAnalysis')
                ->once()
                ->with("{$id}.")
                ->andReturn($analysis);
            return $analyzer;
        });

        $checker = new Checker($this->base_path());
        $returned_analysis = $checker->check($id);

        $this->assertEquals($this->base_path('lower-speck.json'), $caught_config_file);
        $this->assertEquals($this->base_path('requirements.lwr'), $caught_requirements_file);
        $this->assertEquals([$this->base_path('x'), $this->base_path('y'), $this->base_path('z')], $caught_paths_list);
        $this->assertEquals($spec, $caught_specification);
        $this->assertEquals($grepper, $caught_grepper);
        $this->assertEquals($analysis, $returned_analysis);
    }
}
