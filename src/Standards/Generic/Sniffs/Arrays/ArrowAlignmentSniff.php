<?php
/**
 * Ensures that array arrows are aligned correctly.
 *
 * @author    Vladyslav Kochkarov <kochkarov.vladyslav@airslate.com>
 * @copyright 2023 Vladyslav Kochkarov
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Standards\Generic\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ArrowAlignmentSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array
     */
    public function register()
    {
        return [T_OPEN_SHORT_ARRAY];
    }//end register()

    /**
     * Processes this sniff when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Skip if array is not multiline
        if (isset($tokens[$stackPtr]['bracket_closer']) === false) {
            return;
        }
        
        $arrayStart = $stackPtr;
        $arrayEnd   = $tokens[$stackPtr]['bracket_closer'];
        
        if ($tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line']) {
            return;
        }
        
        // Find all double arrows at each indentation level
        $arrowsByIndent = [];
        
        for ($i = $arrayStart + 1; $i < $arrayEnd; $i++) {
            // Skip nested arrays
            if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY && isset($tokens[$i]['bracket_closer']) === true) {
                $i = $tokens[$i]['bracket_closer'];
                continue;
            }
            
            if ($tokens[$i]['code'] === T_DOUBLE_ARROW) {
                // Get indentation by finding the first token on the line
                $firstTokenOnLine = $i;
                while ($firstTokenOnLine > 0 && $tokens[$firstTokenOnLine - 1]['line'] === $tokens[$i]['line']) {
                    $firstTokenOnLine--;
                }
                
                // Find the actual first non-whitespace token
                while ($firstTokenOnLine < $i && $tokens[$firstTokenOnLine]['code'] === T_WHITESPACE) {
                    $firstTokenOnLine++;
                }
                
                $indentLevel = $tokens[$firstTokenOnLine]['column'];
                
                if (isset($arrowsByIndent[$indentLevel]) === false) {
                    $arrowsByIndent[$indentLevel] = [
                        'arrows'        => [],
                        'max_key_length' => 0,
                    ];
                }
                
                // Find the key before the arrow
                $keyStart = $firstTokenOnLine;
                $keyEnd   = $phpcsFile->findPrevious(T_WHITESPACE, ($i - 1), $keyStart, true);
                
                if ($keyEnd !== false) {
                    $keyLength = ($tokens[$keyEnd]['column'] + strlen($tokens[$keyEnd]['content'])) - $tokens[$keyStart]['column'];
                    
                    if ($keyLength > $arrowsByIndent[$indentLevel]['max_key_length']) {
                        $arrowsByIndent[$indentLevel]['max_key_length'] = $keyLength;
                    }
                    
                    $arrowsByIndent[$indentLevel]['arrows'][] = [
                        'arrow_ptr'  => $i,
                        'key_start'  => $keyStart,
                        'key_end'    => $keyEnd,
                        'key_length' => $keyLength,
                    ];
                }
            }//end if
        }//end for
        
        // Now fix the arrows for each indentation level
        foreach ($arrowsByIndent as $indentLevel => $indentData) {
            $maxKeyLength = $indentData['max_key_length'];
            
            foreach ($indentData['arrows'] as $arrow) {
                $arrowPtr  = $arrow['arrow_ptr'];
                $keyStart  = $arrow['key_start'];
                $keyEnd    = $arrow['key_end'];
                $keyLength = $arrow['key_length'];
                
                // Calculate the expected position for the arrow
                $expectedColumn = ($tokens[$keyStart]['column'] + $maxKeyLength + 1);
                
                if ($tokens[$arrowPtr]['column'] !== $expectedColumn) {
                    $error    = 'Array double arrow not aligned correctly; expected column %s but found %s';
                    $data     = [
                        $expectedColumn,
                        $tokens[$arrowPtr]['column'],
                    ];
                    
                    $fix = $phpcsFile->addFixableError($error, $arrowPtr, 'DoubleArrowNotAligned', $data);
                    
                    if ($fix === true) {
                        $phpcsFile->fixer->beginChangeset();
                        
                        // Clear existing whitespace before the arrow
                        if (($keyEnd + 1) < $arrowPtr) {
                            for ($i = ($keyEnd + 1); $i < $arrowPtr; $i++) {
                                if ($tokens[$i]['code'] === T_WHITESPACE) {
                                    $phpcsFile->fixer->replaceToken($i, '');
                                }
                            }
                        }
                        
                        // Add the right amount of spaces
                        $spaces = $expectedColumn - ($tokens[$keyEnd]['column'] + strlen($tokens[$keyEnd]['content']));
                        if ($spaces <= 0) {
                            $spaces = 1;
                        }
                        
                        $phpcsFile->fixer->addContent($keyEnd, str_repeat(' ', $spaces));
                        
                        // Ensure exactly one space after the arrow
                        if (($arrowPtr + 1) < $arrayEnd && $tokens[($arrowPtr + 1)]['code'] === T_WHITESPACE) {
                            // Replace with a single space
                            $phpcsFile->fixer->replaceToken(($arrowPtr + 1), ' ');
                            
                            // Remove any extra whitespace tokens
                            $i = ($arrowPtr + 2);
                            while ($i < $arrayEnd && 
                                   $tokens[$i]['code'] === T_WHITESPACE && 
                                   $tokens[$i]['line'] === $tokens[$arrowPtr]['line']) {
                                $phpcsFile->fixer->replaceToken($i, '');
                                $i++;
                            }
                        } else if (($arrowPtr + 1) < $arrayEnd && 
                                  $tokens[($arrowPtr + 1)]['line'] === $tokens[$arrowPtr]['line']) {
                            // No whitespace after the arrow, add one
                            $phpcsFile->fixer->addContent($arrowPtr, ' ');
                        }
                        
                        $phpcsFile->fixer->endChangeset();
                    }//end if
                }//end if
            }//end foreach
        }//end foreach
        
        // Process nested arrays
        for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
            if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY && isset($tokens[$i]['bracket_closer']) === true) {
                $this->process($phpcsFile, $i);
                $i = $tokens[$i]['bracket_closer'];
            }
        }
    }//end process()
} 