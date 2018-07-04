<?php

namespace LowerSpeck;

use TestCase;
use Mockery;

class ReporterTest extends TestCase
{

    public function testInstantiate()
    {
        new Reporter(new Analysis([]), Reporter::NORMAL);
    }

    /**
     * @LWR 1.g. The class MUST return an analysis of the requirements and 
     * state of code.
     *
     * @LWR 1.g.b. The analysis MUST include progress as the percentage of
     * requirements that have been addressed.
     * 
     * @LWR 1.g.c. The analysis MUST include the number of requirements that 
     * are not obsolete.
     *
     * @LWR 1.g.d. The analysis MUST include the number of requirements that
     * have been addressed and are not obsolete.
     *
     * @LWR 1.g.e. The analysis SHOULD include the number of requirements that
     * are obsolete.
     *
     * @LWR 2.b.d. In double-verbose mode and above the script MUST output all 
     * requirements.
     *
     * @LWR 1.g.f.h. The analysis MUST include a flag with any requirements 
     * that are not addressed.
     *
     * @LWR 1.g.f.c. The analysis MUST include a warning with any requirements
     * that are incomplete.
     *
     * @LWR 1.g.f.i. The analysis MUST include a flag with any requirements 
     * that are obsoleted.
     *
     * @LWR 1.g.f.a. The analysis MUST include a warning with any requirements 
     * that do not use the keywords defined in RFC 2119.
     *
     * @LWR 2.a.a. The script SHOULD output the number of warnings due to 
     * missing RFC 2119 keywords.
     * 
     * @LWR 2.a.b. The script SHOULD output the number of warnings due to 
     * unexpected custom flags.
     *
     * @LWR 2.a.c. The script SHOULD output the number of errors due to 
     * failure to parse.
     *
     * @LWR 2.a.d. The script SHOULD output the number of errors due to gaps.
     * 
     * @LWR 2.a.f. The script SHOULD output the number of errors due to 
     * requirements being out of order.
     *
     * @LWR 1.g.f.d.a. The analysis MUST include parse errors even for
     * requirements that do not fall within the super ID 
     * supplied to the class.
     */
    public function testReportAFewThings_VeryVerbose()
    {
        $analysis = new Analysis();
        $analysis->progress = 60;
        $analysis->active = 5;
        $analysis->addressed = 3;
        $analysis->obsolete = 1;
        $analysis->requirements = [
            new RequirementAnalysis([
                'line' => '1. (X) Must love dogs',
                'is_obsolete' => true,
            ]),
            new RequirementAnalysis([
                'line' => '2. (I) John Tucker must die',
                'is_incomplete' => true,
            ]),
            new RequirementAnalysis([
                'line' => '  2.a. This must be the place',
            ]),

            new RequirementAnalysis([]),

            new RequirementAnalysis([
                'line' => '3. The gods must be crazy',
            ]),
            new RequirementAnalysis([
                'line' => '  3.a. Funny you should ask',
                'is_pending' => true,
            ]),  
            new RequirementAnalysis([
                'line' => '  3.b. It shood happen to you',
                'is_pending' => true,
                'has_warning' => true,
                'notes' => ['Well-written requirements use RFC 2119 keywords such as MUST, SHOULD, and MAY'],
            ]),

            new RequirementAnalysis([]),

            new RequirementAnalysis([
                'line'            => 'PARSE ERROR',
                'has_parse_error' => true,
            ]),
        ];
        $analysis->rfc2119WarningCount = 1;
        $analysis->customFlagWarningCount = 2;
        $analysis->parseFailureCount = 3;
        $analysis->gapErrorCount = 4;

        $reporter = new Reporter($analysis, Reporter::VERY_VERBOSE);

        ob_start();
        $reporter->report();
        $output = ob_get_clean();

        $this->see(
              '+-------+-------------------------------------------------------------------------------+'
            . '| State | Requirement                                                                   |'
            . '+-------+-------------------------------------------------------------------------------+'
            . '| X     | 1. (X) Must love dogs                                                         |'
            . '| I     | 2. (I) John Tucker must die                                                   |'
            . '|       |   2.a. This must be the place                                                 |'
            . '|       |                                                                               |'
            . '|       | 3. The gods must be crazy                                                     |'
            . '| -     |   3.a. Funny you should ask                                                   |'
            . '| -?    |   3.b. It shood happen to you                                                 |'
            . '|       | Well-written requirements use RFC 2119 keywords such as MUST, SHOULD, and MAY |'
            . '|       |                                                                               |'
            . '|       | PARSE ERROR                                                                   |'
            . '+-------+-------------------------------------------------------------------------------+'
            . 'Progress: 60%'
            . 'Requirements: 5'
            . 'Addressed: 3'
            . 'Obsolete: 1'
            . '1 requirement uses weak language.'
            . '2 requirements use bad flags.'
            . '3 requirements cannot be parsed.'
            . '4 requirements are out of order.'
            . 'Use -v or -vv to see more information.',
            $output
        );
        
    }

    /**
     * @LWR 1.g. The class MUST return an analysis of the requirements and 
     * state of code.
     *
     * @LWR 1.g.b. The analysis MUST include progress as the percentage of
     * requirements that have been addressed.
     * 
     * @LWR 1.g.c. The analysis MUST include the number of requirements that 
     * are not obsolete.
     *
     * @LWR 1.g.d. The analysis MUST include the number of requirements that
     * have been addressed and are not obsolete.
     *
     * @LWR 1.g.e. The analysis SHOULD include the number of requirements that
     * are obsolete.
     *
     * @LWR 2.b.d. In double-verbose mode and above the script MUST output all 
     * requirements.
     * 
     * @LWR 1.g.f.a. The analysis MUST include a warning with any requirements 
     * that do not use the keywords defined in RFC 2119.
     *
     * @LWR 1.g.f.b. The analysis MUST include a warning with any requirements
     * that use unknown flags that do not begin with a dash (-).
     *
     * @LWR 1.g.f.c. The analysis MUST include a warning with any requirements
     * that are incomplete.
     * 
     * @LWR 1.g.f.d. The analysis MUST include an error with any requirements 
     * that cannot be parsed.
     * 
     * @LWR 1.g.f.d. The analysis MUST include an error with any requirements 
     * that cannot be parsed.
     * 
     * @LWR 1.g.f.e. The analysis MUST include an error with any requirement
     * immediately following a gap.
     *
     * @LWR 1.g.f.f. The analysis MUST include an error with any requirement
     * with an ID that is duplicated.
     *
     * @LWR 1.g.f.h. The analysis MUST include a flag with any requirements 
     * that are not addressed.
     *
     * @LWR 1.g.f.g. The analysis MUST include an error with any requirement
     * that is out of order.
     *
     * @LWR 2.a.e. The script SHOULD output the number of duplicate ID's.
     */
    public function testReportAllTheThings()
    {
        $analysis = new Analysis();
        $analysis->progress = 1;
        $analysis->active = 2;
        $analysis->addressed = 4;
        $analysis->obsolete = 8;
        $analysis->requirements = [
            new RequirementAnalysis([
                'line'            => 'Wooo',
                'is_inactive'     => true,
                'has_error'       => true,
                'is_obsolete'     => true,
                'is_incomplete'   => true,
                'has_warning'     => true,
                'is_pending'      => true,
                'has_parse_error' => true,
                'notes'           => ['We'],
            ]),
        ];
        $analysis->duplicateIdErrorCount = 5;

        $reporter = new Reporter($analysis, Reporter::VERY_VERBOSE);

        ob_start();
        $reporter->report();
        $output = ob_get_clean();

        $this->see(
              '+-------+-------------+'
            . '| State | Requirement |'
            . '+-------+-------------+'
            . '| X-?!I | Wooo        |'
            . '|       | We          |'
            . '+-------+-------------+'
            . 'Progress: 1%'
            . 'Requirements: 2'
            . 'Addressed: 4'
            . 'Obsolete: 8'
            . '5 requirements use duplicate IDs.'
            . 'Use -v or -vv to see more information.',
            $output
        );
    }

    /**
     * @LWR 1.g. The class MUST return an analysis of the requirements and 
     * state of code.
     *
     * @LWR 1.g.b. The analysis MUST include progress as the percentage of
     * requirements that have been addressed.
     * 
     * @LWR 1.g.c. The analysis MUST include the number of requirements that 
     * are not obsolete.
     *
     * @LWR 1.g.d. The analysis MUST include the number of requirements that
     * have been addressed and are not obsolete.
     *
     * @LWR 1.g.e. The analysis SHOULD include the number of requirements that
     * are obsolete.
     * 
     * @LWR 2.a.g.a In normal mode and above the command must output any 
     * requirements that are not addressed and not obsolete as well as any 
     * incomplete requirements that are addressed and not obsolete.
     *
     * @LWR 1.g.f.h. The analysis MUST include a flag with any requirements 
     * that are not addressed.
     *
     * @LWR 1.g.f.c. The analysis MUST include a warning with any requirements
     * that are incomplete.
     *
     * @LWR 1.g.f.a. The analysis MUST include a warning with any requirements 
     * that do not use the keywords defined in RFC 2119.
     *
     * @LWR 2.a.a. The script SHOULD output the number of warnings due to 
     * missing RFC 2119 keywords.
     * 
     * @LWR 2.a.b. The script SHOULD output the number of warnings due to 
     * unexpected custom flags.
     *
     * @LWR 2.a.c. The script SHOULD output the number of errors due to 
     * failure to parse.
     *
     * @LWR 2.a.d. The script SHOULD output the number of errors due to gaps.
     *
     * @LWR 1.g.f.d.a. The analysis MUST include parse errors even for
     * requirements that do not fall within the super ID 
     * supplied to the class.
     */
    public function testReportFewestThings_Normal()
    {
        $analysis = new Analysis();
        $analysis->progress = 60;
        $analysis->active = 5;
        $analysis->addressed = 3;
        $analysis->obsolete = 1;
        $analysis->requirements = [
            new RequirementAnalysis([
                'line' => '1. (X) Must love dogs',
                'is_obsolete' => true,
            ]),
            new RequirementAnalysis([
                'line' => '2. (I) John Tucker must die',
                'is_incomplete' => true,
            ]),
            new RequirementAnalysis([
                'line' => '  2.a. This must be the place',
            ]),

            new RequirementAnalysis([]),

            new RequirementAnalysis([
                'line' => '3. The gods must be crazy',
            ]),
            new RequirementAnalysis([
                'line' => '  3.a. Funny you should ask',
                'is_pending' => true,
            ]),  
            new RequirementAnalysis([
                'line' => '  3.b. It shood happen to you',
                'is_pending' => true,
                'has_warning' => true,
                'notes' => ['Well-written requirements use RFC 2119 keywords such as MUST, SHOULD, and MAY'],
            ]),

            new RequirementAnalysis([]),

            new RequirementAnalysis([
                'line'            => 'PARSE ERROR',
                'has_parse_error' => true,
            ]),
        ];
        $analysis->rfc2119WarningCount = 1;
        $analysis->customFlagWarningCount = 2;
        $analysis->parseFailureCount = 3;
        $analysis->gapErrorCount = 4;

        $reporter = new Reporter($analysis, Reporter::NORMAL);

        ob_start();
        $reporter->report();
        $output = ob_get_clean();

        $this->see(
              '+-------+-------------------------------------------------------------------------------+'
            . '| State | Requirement                                                                   |'
            . '+-------+-------------------------------------------------------------------------------+'
            . '| I     | 2. (I) John Tucker must die                                                   |'
            . '|       |                                                                               |'
            . '| -     |   3.a. Funny you should ask                                                   |'
            . '| -?    |   3.b. It shood happen to you                                                 |'
            . '|       | Well-written requirements use RFC 2119 keywords such as MUST, SHOULD, and MAY |'
            . '|       |                                                                               |'
            . '|       | PARSE ERROR                                                                   |'
            . '+-------+-------------------------------------------------------------------------------+'
            . 'Progress: 60%'
            . 'Requirements: 5'
            . 'Addressed: 3'
            . 'Obsolete: 1'
            . '1 requirement uses weak language.'
            . '2 requirements use bad flags.'
            . '3 requirements cannot be parsed.'
            . '4 requirements are out of order.'
            . 'Use -v or -vv to see more information.',
            $output
        );
    }

    /**
     * @LWR 1.g. The class MUST return an analysis of the requirements and 
     * state of code.
     *
     * @LWR 1.g.b. The analysis MUST include progress as the percentage of
     * requirements that have been addressed.
     * 
     * @LWR 1.g.c. The analysis MUST include the number of requirements that 
     * are not obsolete.
     *
     * @LWR 1.g.d. The analysis MUST include the number of requirements that
     * have been addressed and are not obsolete.
     *
     * @LWR 1.g.e. The analysis SHOULD include the number of requirements that
     * are obsolete.
     *
     * @LWR 2.b.c. In verbose mode and above the script MUST output all 
     * requirements that are not obsolete.
     *
     * @LWR 1.g.f.h. The analysis MUST include a flag with any requirements 
     * that are not addressed.
     *
     * @LWR 1.g.f.c. The analysis MUST include a warning with any requirements
     * that are incomplete.
     *
     * @LWR 1.g.f.i. The analysis MUST include a flag with any requirements 
     * that are obsoleted.
     *
     * @LWR 1.g.f.a. The analysis MUST include a warning with any requirements 
     * that do not use the keywords defined in RFC 2119.
     *
     * @LWR 2.a.a. The script SHOULD output the number of warnings due to 
     * missing RFC 2119 keywords.
     * 
     * @LWR 2.a.b. The script SHOULD output the number of warnings due to 
     * unexpected custom flags.
     *
     * @LWR 2.a.c. The script SHOULD output the number of errors due to 
     * failure to parse.
     *
     * @LWR 2.a.d. The script SHOULD output the number of errors due to gaps.
     *
     * @LWR 1.g.f.d.a. The analysis MUST include parse errors even for
     * requirements that do not fall within the super ID 
     * supplied to the class.
     */
    public function testReportFewerThings_Verbose()
    {
        $analysis = new Analysis();
        $analysis->progress = 60;
        $analysis->active = 5;
        $analysis->addressed = 3;
        $analysis->obsolete = 1;
        $analysis->requirements = [
            new RequirementAnalysis([
                'line' => '1. (X) Must love dogs',
                'is_obsolete' => true,
            ]),
            new RequirementAnalysis([
                'line' => '2. (I) John Tucker must die',
                'is_incomplete' => true,
            ]),
            new RequirementAnalysis([
                'line' => '  2.a. This must be the place',
            ]),

            new RequirementAnalysis([]),

            new RequirementAnalysis([
                'line' => '3. The gods must be crazy',
            ]),
            new RequirementAnalysis([
                'line' => '  3.a. Funny you should ask',
                'is_pending' => true,
            ]),  
            new RequirementAnalysis([
                'line' => '  3.b. It shood happen to you',
                'is_pending' => true,
                'has_warning' => true,
                'notes' => ['Well-written requirements use RFC 2119 keywords such as MUST, SHOULD, and MAY'],
            ]),

            new RequirementAnalysis([]),

            new RequirementAnalysis([
                'line'            => 'PARSE ERROR',
                'has_parse_error' => true,
            ]),
        ];
        $analysis->rfc2119WarningCount = 1;
        $analysis->customFlagWarningCount = 2;
        $analysis->parseFailureCount = 3;
        $analysis->gapErrorCount = 4;

        $reporter = new Reporter($analysis, Reporter::VERBOSE);

        ob_start();
        $reporter->report();
        $output = ob_get_clean();

        $this->see(
              '+-------+-------------------------------------------------------------------------------+'
            . '| State | Requirement                                                                   |'
            . '+-------+-------------------------------------------------------------------------------+'
            . '| I     | 2. (I) John Tucker must die                                                   |'
            . '|       |   2.a. This must be the place                                                 |'
            . '|       |                                                                               |'
            . '|       | 3. The gods must be crazy                                                     |'
            . '| -     |   3.a. Funny you should ask                                                   |'
            . '| -?    |   3.b. It shood happen to you                                                 |'
            . '|       | Well-written requirements use RFC 2119 keywords such as MUST, SHOULD, and MAY |'
            . '|       |                                                                               |'
            . '|       | PARSE ERROR                                                                   |'
            . '+-------+-------------------------------------------------------------------------------+'
            . 'Progress: 60%'
            . 'Requirements: 5'
            . 'Addressed: 3'
            . 'Obsolete: 1'
            . '1 requirement uses weak language.'
            . '2 requirements use bad flags.'
            . '3 requirements cannot be parsed.'
            . '4 requirements are out of order.'
            . 'Use -v or -vv to see more information.',
            $output
        );
    }

    /**
     * @LWR 2.a.g.a. Repeated blank lines SHOULD be collapsed to one.
     *
     * @LWR 2.a.g.b. Leading and trailing blank lines SHOULD not be returned 
     * in the analysis.
     */
    public function testReportSingleBlanks()
    {
        $analysis = new Analysis();
        $analysis->requirements = [
            new RequirementAnalysis([
                'line' => '1. (X) Must love dogs',
                'is_obsolete' => true,
            ]),
            new RequirementAnalysis([]),
            new RequirementAnalysis([
                'line' => '2. (I) John Tucker must die',
                'is_incomplete' => true,
            ]),
            new RequirementAnalysis([]),
            new RequirementAnalysis([
                'line' => '  2.a. This must be the place',
            ]),

            new RequirementAnalysis([
                'line' => '3. The gods must be crazy',
            ]),
            new RequirementAnalysis([]),
            new RequirementAnalysis([
                'line' => '  3.a. Funny you should ask',
                'is_pending' => true,
            ]),  
            new RequirementAnalysis([]),
            new RequirementAnalysis([]),
            new RequirementAnalysis([
                'line' => '  3.b. It shood happen to you',
                'is_pending' => true,
                'has_warning' => true,
                'notes' => ['Well-written requirements use RFC 2119 keywords such as MUST, SHOULD, and MAY'],
            ]),
            new RequirementAnalysis([]),
        ];

        $reporter = new Reporter($analysis, Reporter::NORMAL);

        ob_start();
        $reporter->report();
        $output = ob_get_clean();

        $this->see(
              '+-------+-------------------------------------------------------------------------------+'
            . '| State | Requirement                                                                   |'
            . '+-------+-------------------------------------------------------------------------------+'
            . '| I     | 2. (I) John Tucker must die                                                   |'
            . '|       |                                                                               |'
            . '| -     |   3.a. Funny you should ask                                                   |'
            . '|       |                                                                               |'
            . '| -?    |   3.b. It shood happen to you                                                 |'
            . '|       | Well-written requirements use RFC 2119 keywords such as MUST, SHOULD, and MAY |'
            . '+-------+-------------------------------------------------------------------------------+',
            $output
        );
    }
}
