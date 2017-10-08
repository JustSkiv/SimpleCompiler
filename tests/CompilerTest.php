<?php
/**
 * User: Nikolay Tuzov
 * Date: 06.10.17
 */

namespace codewars\compiler;

include_once __DIR__ . '/../src/Compiler.php';

use PHPUnit\Framework\TestCase;
use src\codewars\compiler\Compiler;

class CompilerTest extends TestCase
{

    /*public function testSimpleProg() {
        $prog = '[ x y z ] ( 2*3*x + 5*y - 3*z ) / (1 + 3 + 2*2)';
        $c = new Compiler();

        $t1 = '{"op":"/","a":{"op":"-","a":{"op":"+","a":{"op":"*","a":{"op":"*","a":{"op":"imm","n":2},"b":{"op":"imm","n":3}},"b":{"op":"arg","n":0}},"b":{"op":"*","a":{"op":"imm","n":5},"b":{"op":"arg","n":1}}},"b":{"op":"*","a":{"op":"imm","n":3},"b":{"op":"arg","n":2}}},"b":{"op":"+","a":{"op":"+","a":{"op":"imm","n":1},"b":{"op":"imm","n":3}},"b":{"op":"*","a":{"op":"imm","n":2},"b":{"op":"imm","n":2}}}}';
        $p1 = $c->pass1($prog);
        $this->assertEquals($p1, json_decode($t1, true), 'Pass1');

        $t2 = '{"op":"/","a":{"op":"-","a":{"op":"+","a":{"op":"*","a":{"op":"imm","n":6},"b":{"op":"arg","n":0}},"b":{"op":"*","a":{"op":"imm","n":5},"b":{"op":"arg","n":1}}},"b":{"op":"*","a":{"op":"imm","n":3},"b":{"op":"arg","n":2}}},"b":{"op":"imm","n":8}}';
        $p2 = $c->pass2($p1);
        $this->assertEquals($p2, json_decode($t2, true), 'Pass2');

        $p3 = $c->pass3($p2);
        $this->assertEquals(simulate($p3, [4,0,0]), 3, 'prog(4,0,0) == 3');
        $this->assertEquals(simulate($p3, [4,8,0]), 8, 'prog(4,8,0) == 8');
        $this->assertEquals(simulate($p3, [4,8,16]), 2, 'prog(4,8,6) == 2');
    }*/

    /*public function testPass1()
    {
        $c = new Compiler();

        $prog = '[ x y z ] ( 2*3*x + 5*y - 3*z ) / (1 + 3 + 2*2)';

        $t1 = '{"op":"/","a":{"op":"-","a":{"op":"+","a":{"op":"*","a":{"op":"*","a":{"op":"imm","n":2},"b":{"op":"imm","n":3}},"b":{"op":"arg","n":0}},"b":{"op":"*","a":{"op":"imm","n":5},"b":{"op":"arg","n":1}}},"b":{"op":"*","a":{"op":"imm","n":3},"b":{"op":"arg","n":2}}},"b":{"op":"+","a":{"op":"+","a":{"op":"imm","n":1},"b":{"op":"imm","n":3}},"b":{"op":"*","a":{"op":"imm","n":2},"b":{"op":"imm","n":2}}}}';

        $p1 = $c->pass1($prog);

        $this->assertEquals($p1, json_decode($t1, true), 'Pass1');
    }*/

    public function testGetArgs()
    {
        $c = new Compiler();
        $prog = '[ x y z ] ( 2*3*x + 5*y - 3*z ) / (1 + 3 + 2*2)';

        $tokens = $c->tokenize($prog);
        $args = $c->getArgs($tokens);

        $this->assertInternalType('array', $args);
        $this->assertEquals($args[0], 'x', 'check X');
        $this->assertEquals($args[1], 'y', 'check Y');
        $this->assertEquals($args[2], 'z', 'check Z');
        $this->assertCount(3, $args);
    }

    public function testGetBody()
    {
        $c = new Compiler();
        $prog = '[ x y z ] ( 2*3*x + 5*y - 3*z ) / (1 + 3 + 2*2)';

        $tokens = $c->tokenize($prog);
        $bodyParts = $c->getBodyParts($tokens);

        $this->assertInternalType('array', $bodyParts);
        $this->assertEquals($bodyParts[0], '(');
        $this->assertEquals($bodyParts[24], ')');
        $this->assertEquals($bodyParts[7], 5);
        $this->assertCount(25, $bodyParts);
    }

    public function testSlicer()
    {
        $c = new Compiler();
        $prog = '[ x y z ] ( 2*3*x + 5*y - 3*z ) / (1 + 3 + 2*2)';

        $tokens = $c->tokenize($prog);
        $bodyParts = $c->getBodyParts($tokens);
        list($firstPart, $secondPart, $slicer) = $c->sliceBodyParts($bodyParts);

        $this->assertInternalType('array', $firstPart);
        $this->assertInternalType('array', $secondPart);
        $this->assertInternalType('string', $slicer);

        $this->assertEquals($firstPart[0], '(');
        $this->assertEquals($firstPart[14], ')');
        $this->assertEquals($firstPart[4], '*');
        $this->assertEquals($firstPart[7], 5);

        $this->assertEquals($secondPart[0], '(');
        $this->assertEquals($secondPart[8], ')');
        $this->assertEquals($secondPart[2], '+');
        $this->assertEquals($secondPart[5], 2);

        $this->assertEquals($slicer, '/');

        $this->assertCount(15, $firstPart);
        $this->assertCount(9, $secondPart);
    }

    public function testArgAst()
    {
        $argsArray = ['x', 'y', 'z'];
        $arg1 = 'x';
        $arg2 = 'y';


        $arg1AstCorrect = [
            'op' => 'arg',
            'n' => 0
        ];
        $arg2AstCorrect = [
            'op' => 'arg',
            'n' => 1
        ];


        $c = new Compiler();

        $arg1Ast = $c->getArgAst($arg1, $argsArray);
        $arg2Ast = $c->getArgAst($arg2, $argsArray);

        $this->assertEquals($arg1Ast, $arg1AstCorrect);
        $this->assertEquals($arg2Ast, $arg2AstCorrect);
    }

    public function testIntAst()
    {
        $int1 = 1;
        $int2 = 123;

        $int1AstCorrect = [
            'op' => 'imm',
            'n' => 1
        ];
        $int2AstCorrect = [
            'op' => 'imm',
            'n' => 123
        ];


        $c = new Compiler();

        $int1Ast = $c->getIntAst($int1);
        $int2Ast = $c->getIntAst($int2);

        $this->assertEquals($int1Ast, $int1AstCorrect);
        $this->assertEquals($int2Ast, $int2AstCorrect);
    }

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
            ['[ x y ] x + y', ['op' => '+', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => 'arg', 'n' => 1]]],
            ['[ asd gg ] gg * asd', ['op' => '*', 'a' => ['op' => 'arg', 'n' => 1], 'b' => ['op' => 'arg', 'n' => 0]]],
            ['[ x y z ] x + y + z', ['op' => '+', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => '+', 'a' => ['op' => 'arg', 'n' => 1], 'b' => ['op' => 'arg', 'n' => 2]]]],
            ['[ x y z ] x * y - z', ['op' => '*', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => '-', 'a' => ['op' => 'arg', 'n' => 1], 'b' => ['op' => 'arg', 'n' => 2]]]],
//            ['[ x y z ] x + y * z', ['op' => '*', 'a' => ['op' => 'arg', 'n' => 0], 'b' => ['op' => '-', 'a' => ['op' => 'arg', 'n' => 1], 'b' => ['op' => 'arg', 'n' => 2]]]],



        ];
    }

    /*public function testPass2()
    {
        $c = new Compiler();

        $t1 = '{"op":"/","a":{"op":"-","a":{"op":"+","a":{"op":"*","a":{"op":"*","a":{"op":"imm","n":2},"b":{"op":"imm","n":3}},"b":{"op":"arg","n":0}},"b":{"op":"*","a":{"op":"imm","n":5},"b":{"op":"arg","n":1}}},"b":{"op":"*","a":{"op":"imm","n":3},"b":{"op":"arg","n":2}}},"b":{"op":"+","a":{"op":"+","a":{"op":"imm","n":1},"b":{"op":"imm","n":3}},"b":{"op":"*","a":{"op":"imm","n":2},"b":{"op":"imm","n":2}}}}';
        $p1 = json_decode($t1, true);

        $t2 = '{"op":"/","a":{"op":"-","a":{"op":"+","a":{"op":"*","a":{"op":"imm","n":6},"b":{"op":"arg","n":0}},"b":{"op":"*","a":{"op":"imm","n":5},"b":{"op":"arg","n":1}}},"b":{"op":"*","a":{"op":"imm","n":3},"b":{"op":"arg","n":2}}},"b":{"op":"imm","n":8}}';

        $p2 = $c->pass2($p1);

        $this->assertEquals($p2, json_decode($t2, true), 'Pass2');
    }*/
}
