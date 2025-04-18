<?php
/**
 * Unit test class for the ArrowAlignment sniff.
 *
 * @author    Vladyslav Rudenko
 * @copyright 2023 Vladyslav Rudenko
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Standards\Generic\Tests\Arrays;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class ArrowAlignmentUnitTest extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @param string $testFile The name of the file being tested.
     *
     * @return array<int, int>
     */
    public function getErrorList($testFile='ArrowAlignmentUnitTest.inc')
    {
        return [];
    }//end getErrorList()

    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @param string $testFile The name of the file being tested.
     *
     * @return array<int, int>
     */
    public function getWarningList($testFile='ArrowAlignmentUnitTest.inc')
    {
        return [
            5  => 1,
            6  => 1,
            12 => 1,
            15 => 1,
            24 => 1,
            30 => 2,
            31 => 2,
            33 => 2,
            34 => 1,
        ];
    }//end getWarningList()
} 