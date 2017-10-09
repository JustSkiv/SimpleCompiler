<?php
/**
 * User: Nikolay Tuzov
 * Date: 06.10.17
 */

namespace codewars\compiler;

include_once __DIR__ . '/../src/Compiler.php';

use PHPUnit\Framework\TestCase;
use src\codewars\compiler\Compiler;

class CompilerTest2 extends TestCase
{

    /**
     * @dataProvider progsProvider
     */
    public function testProcessProg($prog, $ast)
    {
        $c = new Compiler();
        $tokens = $c->tokenize($prog);
        $args = $c->getArgs($tokens);
        $bodyParts = $c->getBodyParts($tokens);

        $processed = $c->processProg($bodyParts, $args);

        $this->assertEquals($processed, $ast);
    }

    public function progsProvider()
    {
        return [
//            ['[ x y ] x + y', ['op' => '+', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => 'arg', 'n' => 1]]],
//            ['[ asd gg ] gg * asd', ['op' => '*', 'a' => ['op' => 'arg', 'n' => 1], 'b' => ['op' => 'arg', 'n' => 0]]],
//            ['[ x y z ] x + y + z', ['op' => '+', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => '+', 'a' => ['op' => 'arg', 'n' => 1], 'b' => ['op' => 'arg', 'n' => 2]]]],
//            ['[ x y z ] x * y - z', ['op' => '*', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => '-', 'a' => ['op' => 'arg', 'n' => 1], 'b' => ['op' => 'arg', 'n' => 2]]]],
//            ['[ x y z ] x + y * z', ['op' => '*', 'a' => ['op' => '+', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => 'arg', 'n' => 1]], 'b' => ['op' => 'arg', 'n' => 2]]],
//            ['[ x y z ] (x + y) + z', ['op' => '+', 'a' => ['op' => '+', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => 'arg', 'n' => 1]], 'b' => ['op' => 'arg', 'n' => 2]]],
            ['[ x y z ] ((x + y) + (a + b)) + z', ['op' => '+', 'a' => ['op' => '+', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => 'arg', 'n' => 1]], 'b' => ['op' => 'arg', 'n' => 4]]],

        ];
    }
}
