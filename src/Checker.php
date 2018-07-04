<?php

namespace LowerSpeck;

class Checker
{
    private $base_path;

    public function __construct(string $base_path)
    {
        if (substr($base_path, -1) != '/') {
            $base_path .= '/';
        }
        $this->base_path = $base_path;
    }

    public function check($id = null)
    {
        $config = Make::make(Config::class, [$this->base_path . 'lower-speck.json']);

        $specification = Make::make(Parser::class, [$this->base_path . 'requirements.lwr'])->getSpecification();

        $paths = array_map(function ($path) {
            return $this->base_path . $path;
        }, $config->paths());

        $grepper = Make::make(ReferenceGrepper::class, [$paths]);

        return Make::make(Analyzer::class, [
                $specification,
                $grepper,
            ])
            ->getAnalysis($id);
    }
}