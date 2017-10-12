<?php
/**
 * User: Nikolay Tuzov
 * Date: 06.10.17
 */

namespace src\codewars\compiler;

class Compiler
{
    /*public function compile($program)
    {
        return $this->pass3($this->pass2($this->pass1($program)));
    }*/

    public function tokenize($program)
    {
        /*
         * Turn a program string into an array of tokens.  Each token
         * is either '[', ']', '(', ')', '+', '-', '*', '/', a variable
         * name or a number (as a string)
         */
        $tokens = preg_split('/\s+/', trim(preg_replace('/([-+*\/\(\)\[\]])/', ' $1 ', $program)));
        foreach ($tokens as &$token) {
            if (is_numeric($token)) {
                $token = intval($token);
            }
        }
        return $tokens;
    }

    public function pass1($program)
    {
        // Returns an un-optimized AST
        $tokens = $this->tokenize($program);

        $args = $this->getArgs($tokens);
        $bodyParts = $this->getBodyParts($tokens);
        $processed = $this->processProg($bodyParts, $args);

        return $processed;
    }

    public function pass2($ast)
    {
        // Returns an AST with constant expressions reduced
        return $this->simplify($ast);
    }

    public function pass3($ast)
    {
        // Returns assembly instructions
        return false;
    }

    /**
     * Get Args from program tokens
     *
     * @param $programTokens
     * @return array
     */
    public function getArgs($programTokens)
    {
        $args = [];
        $token = array_shift($programTokens);

        $readingArgs = false; // we can read args
        while ($token != ']') {
            // this token is arg
            if ($readingArgs) {
                $args[] = $token;
            }

            // next token will be arg
            if ($token == '[') {
                $readingArgs = true;
            } elseif ($token == ']') {
                break;
            }

            $token = array_shift($programTokens);
        }

        return $args;
    }

    /**
     * Get Body from program tokens
     *
     * @param $programTokens
     * @return array
     */
    public function getBodyParts($programTokens)
    {
        $bodyParts = [];

        $readingBody = false; // we can read body
        foreach ($programTokens as $token) {
            // this token is body
            if ($readingBody) {
                $bodyParts[] = $token;
            }

            // next token will be body
            if ($token == ']') {
                $readingBody = true;
            }
        }

        return $bodyParts;
    }

    public function processProg($bodyParts, $args)
    {
        if (count($bodyParts) == 1) {
            $token = $bodyParts[0];
            $ast = '';
            if (is_string($token)) {
                $ast = $this->getArgAst($token, $args);
            } elseif (is_int($token)) {
                $ast = $this->getIntAst($token);
            }
            return $ast;
        }

        list($firstPart, $secondPart, $slicer) = $this->sliceBodyParts($bodyParts);

        $firstPartProcessed = $this->processProg($firstPart, $args);
        $secondPartProcessed = $this->processProg($secondPart, $args);

        $ast = [
            'op' => $slicer,
            'a' => $firstPartProcessed,
            'b' => $secondPartProcessed,
        ];


        return $ast;
    }

    /**
     * Get correct Ast for arg
     *
     * @param $arg
     * @param $argsArray
     * @return array
     */
    public function getArgAst($arg, $argsArray)
    {
        $argsFlipped = array_flip($argsArray);
        $argNum = $argsFlipped[$arg];

        $ast = [
            'op' => 'arg',
            'n' => $argNum
        ];

        return $ast;
    }

    public function getIntAst($int)
    {
        $ast = [
            'op' => 'imm',
            'n' => $int
        ];

        return $ast;
    }

    /**
     * Slice sub-program into two parts (ie get op, a, b)
     *
     * @param $bodyParts
     * @return array
     */
    public function sliceBodyParts($bodyParts): array
    {
        // find arg-slicer num
        $slicerNum = false;
        $slicer = false;

        // arg groups, sorted by priority
        $argsSlicersGroups = [
            ['+', '-'], // priority 1
            ['*', '/'], // priority 2
        ];

        // because we need left associative
        $bodyParts = array_reverse($bodyParts);

        foreach ($argsSlicersGroups as $argsSlicers) {
            $openBracketsCount = 0; //if in brackets, skip slicers
            foreach ($bodyParts as $n => $token) {
                if ($openBracketsCount == 0 && in_array($token, $argsSlicers)) {
                    $slicerNum = $n;
                    $slicer = $token;
                    break;
                }

                if ($slicer) {
                    break;
                }

                if ($token == '(') {
                    $openBracketsCount++;
                } elseif ($token == ')') {
                    $openBracketsCount--;
                }
            }
        }

        // undo revert (left associative)
        $secondPartReverted = array_slice($bodyParts, 0, $slicerNum);
        $firsrtPartReverted = array_slice($bodyParts, $slicerNum + 1);

        $secondPart = array_reverse($secondPartReverted);
        $firstPart = array_reverse($firsrtPartReverted);


        $needSliceBracketsFirstPart = count($firstPart) > 2 && $firstPart[0] == '(' && $firstPart[count($firstPart) - 1] == ')';
        if ($needSliceBracketsFirstPart) {
            $firstPartTmp = array_slice($firstPart, 1, count($firstPart) - 2);

            $opened = 0;
            foreach ($firstPartTmp as $token) {
                if ($token == '(') {
                    $opened++;
                } elseif ($token == ')') {
                    $opened--;
                }

                if ($opened < 0) {
                    break;
                }
            }

            if ($opened == 0) {
                $firstPart = $firstPartTmp;
            }
        }

        $needSliceBracketsSecondPart = count($secondPart) > 2 && $secondPart[0] == '(' && $secondPart[count($secondPart) - 1] == ')';
        if ($needSliceBracketsSecondPart) {
            $secondPartTmp = array_slice($secondPart, 1, count($secondPart) - 2);

            $opened = 0;
            foreach ($secondPartTmp as $token) {
                if ($token == '(') {
                    $opened++;
                } elseif ($token == ')') {
                    $opened--;
                }

                if ($opened < 0) {
                    break;
                }
            }

            if ($opened == 0) {
                $secondPart = $secondPartTmp;
            }
        }

        $res = [$firstPart, $secondPart, $slicer];
        return $res;
    }

    public function simplify($ast)
    {
        if ($ast['op'] == 'arg' || $ast['op'] == 'imm') {
            return $ast;
        }

        $a = self::simplify($ast['a']);
        $b = self::simplify($ast['b']);


        $ast['a'] = $a;
        $ast['b'] = $b;
        $result = $ast;

        if ($a['op'] == 'imm' && $b['op'] == 'imm') {
            switch ($ast['op']) {
                case '+':
                    $result = ['op' => 'imm', 'n' => $a['n'] + $b['n']];
                    break;
                case '-':
                    $result = ['op' => 'imm', 'n' => $a['n'] - $b['n']];
                    break;
                case '*':
                    $result = ['op' => 'imm', 'n' => $a['n'] * $b['n']];
                    break;
                case '/':
                    $result = ['op' => 'imm', 'n' => $a['n'] / $b['n']];
                    break;
            }
        }

        return $result;
    }

    public function assemble($ast)
    {
        $commands = [];

        if (self::isOperation($ast)) {
            // work with arg A
            if (self::isTrivial($ast['a'])) {
                $commands[] = self::commandLoad($ast['a']);
            } else {
                // assemble this arg
                $subCommands = $this->assemble($ast['a']);

                // TODO: need to do smth with older results (swap etc)
                $commands = array_merge($commands, $subCommands);

                // TODO: mb, it better to place at end of funtion?
                $commands[] = 'PU';
            }

            $commands[] = 'SW';

            // work with arg B
            if (self::isTrivial($ast['b'])) {
                $commands[] = self::commandLoad($ast['b']);
            } else {
                return false;
            }

            $commands[] = self::commandForOperation($ast);
        }

        return $commands;
    }

    public static function commandLoad($ast)
    {
        if (!self::isTrivial($ast)) {
            return false;
        }

        $command = false;

        switch ($ast['op']) {
            case 'arg':
                $command = 'AR';
                break;
            case 'imm':
                $command = 'IM';
                break;
        }

        $command .= ' ' . $ast['n'];

        return $command;
    }

    public static function commandForOperation($ast)
    {
        if (!self::isOperation($ast)) {
            return false;
        }
        $command = false;

        switch ($ast['op']) {
            case '+':
                $command = 'AD';
                break;
            case '-':
                $command = 'SU';
                break;
            case '*':
                $command = 'MU';
                break;
            case '/':
                $command = 'DI';
                break;
        }

        return $command;
    }

    /**
     * Is it operation ( *, /, + or - )
     *
     * @param $ast
     * @return bool
     */
    public static function isOperation($ast)
    {
        $operationSigns = ['*', '/', '+', '-',];

        return in_array($ast['op'], $operationSigns);
    }

    /**
     * Is it arg or imm
     *
     * @param $ast
     * @return bool
     */
    public static function isTrivial($ast)
    {
        $trivialElements = ['imm', 'arg'];

        return in_array($ast['op'], $trivialElements);
    }

    /**
     * Trivial operation: <arg|imm> <operation> <arg|imm>
     * Example: x + 5, 23 / y, etc
     *
     * @param $ast
     * @return bool
     */
    public static function isTrivialOperation($ast)
    {
        $isOperationTrivial = self::isOperation($ast);
        $isATrivial = isset($ast['a']) && self::isTrivial($ast['a']);
        $isBTrivial = isset($ast['b']) && self::isTrivial($ast['b']);

        return $isOperationTrivial && $isATrivial && $isBTrivial;
    }

    // Here is a simulator for the target machine.
    // It takes an array of assembly instructions and an array of arguments and returns the result.
    function simulate($asm, $argv)
    {
        list($r0, $r1) = [0, 0];
        $stack = [];
        foreach ($asm as $ins) {
            if (substr($ins, 0, 2) == 'IM' || substr($ins, 0, 2) == 'AR') {
                list($ins, $n) = [substr($ins, 0, 2), intval(substr($ins, 2))];
            }
            if ($ins == 'IM') $r0 = $n;
            else if ($ins == 'AR') $r0 = $argv[$n];
            else if ($ins == 'SW') list($r0, $r1) = [$r1, $r0];
            else if ($ins == 'PU') array_push($stack, $r0);
            else if ($ins == 'PO') $r0 = array_pop($stack);
            else if ($ins == 'AD') $r0 += $r1;
            else if ($ins == 'SU') $r0 -= $r1;
            else if ($ins == 'MU') $r0 *= $r1;
            else if ($ins == 'DI') $r0 /= $r1;
        }
        return $r0;
    }

}