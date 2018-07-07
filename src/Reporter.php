<?php

namespace LowerSpeck;

use Illuminate\Support\Collection;

class Reporter
{
    private $analysis;
    private $verbosity;

    const NORMAL       = 0;
    const VERBOSE      = 1;
    const VERY_VERBOSE = 2;

    public function __construct(Analysis $analysis, int $verbosity)
    {
        $this->analysis = $analysis;
        $this->verbosity = $verbosity;
    }

    public function report()
    {
        $previous_was_blank = null;
        $table_data = collect($this->analysis->requirements)
            // Filter out according to verbosity
            ->filter(function ($requirement_analysis) {
                if (!trim($requirement_analysis->line)) {
                    return true;
                }
                if ($this->verbosity === static::NORMAL) {
                    if ($requirement_analysis->is_obsolete) {
                        return false;
                    }
                    if ($requirement_analysis->is_pending) {
                        return true;
                    }
                    if ($requirement_analysis->is_incomplete) {
                        return true;
                    }
                    if ($requirement_analysis->has_parse_error) {
                        return true;
                    }
                    return false;
                }
                if ($this->verbosity === static::VERBOSE) {
                    if ($requirement_analysis->is_obsolete) {
                        return false;
                    }
                    return true;
                }
                return true;
            })
            // Filter out double-blank-lines
            ->filter(function ($requirement_analysis) use (&$previous_was_blank) {
                $is_blank = trim($requirement_analysis->line) == '';
                if ($is_blank && $previous_was_blank) {
                    return false;
                }
                $previous_was_blank = $is_blank;
                return true;
            })
            // Turn analysis entries into arrays compatible with `table()`
            ->map(function ($requirement_analysis) {
                $line = array_merge(
                    [$requirement_analysis->line],
                    $requirement_analysis->notes
                );
                $flags = [];
                if ($requirement_analysis->is_obsolete) {
                    $flags[] = 'X';
                }
                if ($requirement_analysis->is_pending) {
                    $flags[] = '-';
                }
                if ($requirement_analysis->has_warning) {
                    $flags[] = '?';
                }
                if ($requirement_analysis->has_error) {
                    $flags[] = '!';
                }
                if ($requirement_analysis->is_incomplete) {
                    $flags[] = 'I';
                }
                return [implode('', $flags), implode("\n", $line)];
            })
            ->values()
            ->all();

        // remove leading blank
        if (!trim($table_data[0][1])) {
            array_shift($table_data);
        }

        // remove trailing blank
        if (isset($table_data[count($table_data) - 1][1]) && !trim($table_data[count($table_data) - 1][1])) {
            array_pop($table_data);
        }

        $this->table(
            ['State', 'Requirement'],
            $table_data
        );

        $this->info("Progress: {$this->analysis->progress}%");
        $this->info("Requirements: {$this->analysis->active}");
        $this->info("Addressed: {$this->analysis->addressed}");
        $this->info("Obsolete: {$this->analysis->obsolete}");

        if ($this->analysis->rfc2119WarningCount) {
            $this->comment(
                $this->analysis->rfc2119WarningCount == 1
                ? "1 requirement uses weak language."
                : "{$this->analysis->rfc2119WarningCount} requirements use weak language."
            );
        }

        if ($this->analysis->customFlagWarningCount) {
            $this->comment(
                $this->analysis->customFlagWarningCount == 1
                ? "1 requirement uses bad flags."
                : "{$this->analysis->customFlagWarningCount} requirements use bad flags."
            );
        }

        if ($this->analysis->parseFailureCount) {
            $this->error(
                $this->analysis->parseFailureCount == 1
                ? "1 requirement cannot be parsed."
                : "{$this->analysis->parseFailureCount} requirements cannot be parsed."
            );
        }

        if ($this->analysis->gapErrorCount) {
            $this->error(
                $this->analysis->gapErrorCount == 1
                ? "1 requirement is out of order."
                : "{$this->analysis->gapErrorCount} requirements are out of order."
            );
        }

        if ($this->analysis->duplicateIdErrorCount) {
            $this->error(
                $this->analysis->duplicateIdErrorCount == 1
                ? "1 requirement uses a duplicate ID."
                : "{$this->analysis->duplicateIdErrorCount} requirements use duplicate IDs."
            );
        }

        $this->line('Use -v or -vv to see more information.');
    }

    private function table($headers, $data)
    {
        $col_widths = [];
        collect($data)->merge([$headers])
            ->each(function ($row) use (&$col_widths) {
                collect($row)
                    ->each(function ($cell, $i) use (&$col_widths) {
                        $width = $this->getWidth($cell);
                        if (!isset($col_widths[$i]) || $col_widths[$i] < $width) {
                            $col_widths[$i] = $width;
                        }
                    });
            });

        $bar = '+' 
            . collect($col_widths)
                ->map(function ($width) {
                    return str_repeat('-', $width + 2); // spacing
                })
                ->implode('+')
            . '+';

        echo "{$bar}\n";
        $this->row($headers, $col_widths);
        echo "{$bar}\n";
        collect($data)
            ->each(function ($row) use ($col_widths) {
                $this->row($row, $col_widths);
            });
        echo "{$bar}\n";
    }

    private function getWidth($data) : int
    {
        $parts = preg_split('/\n/', $data);
        return collect($parts)
            ->reduce(function ($size, $part) {
                return strlen($part) > $size ? strlen($part) : $size;
            }, 0);
    }

    private function getHeight($row) : int
    {
        return collect($row)
            ->reduce(function ($height, $cell) {
                $new_height = count(preg_split('/\n/', $cell));
                return $new_height > $height ? $new_height : $height;
            }, 0);
    }

    private function row($row, $col_widths)
    {
        $height = $this->getHeight($row);
        $columns = collect($row)
            ->map(function ($cell) use ($height) {
                return array_pad(preg_split('/\n/', $cell), $height, '');
            })
            ->toArray();
        $lines = [];
        foreach ($columns as $i => $cell_parts) {
            foreach ($cell_parts as $j => $part) {
                $lines[$j][$i] = $part;
            }
        }
        collect($lines)
            ->each(function ($line) use ($col_widths) {
                echo '|'
                    . collect($line)
                        ->map(function ($cell, $i) use ($col_widths) {
                            $width = $col_widths[$i];
                            $remaining = $width - strlen($cell);
                            return " {$cell} " . str_repeat(' ', $remaining > 0 ? $remaining : 0);
                        })
                        ->implode('|')
                    . "|\n";
            });
    }

    private function info($text)
    {
        echo "{$text}\n";
    }

    private function comment($text)
    {
        echo "{$text}\n";
    }

    private function error($text)
    {
        echo "{$text}\n";
    }

    private function line($text)
    {
        echo "{$text}\n";
    }
}

