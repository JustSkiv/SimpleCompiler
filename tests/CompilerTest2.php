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
    public function testSimpleProg() {
        $prog = '[ x y z ] ( 2*3*x + 5*y - 3*z ) / (1 + 3 + 2*2)';
        $c = new Compiler();

        $t1 = '{"op":"/","a":{"op":"-","a":{"op":"+","a":{"op":"*","a":{"op":"*","a":{"op":"imm","n":2},"b":{"op":"imm","n":3}},"b":{"op":"arg","n":0}},"b":{"op":"*","a":{"op":"imm","n":5},"b":{"op":"arg","n":1}}},"b":{"op":"*","a":{"op":"imm","n":3},"b":{"op":"arg","n":2}}},"b":{"op":"+","a":{"op":"+","a":{"op":"imm","n":1},"b":{"op":"imm","n":3}},"b":{"op":"*","a":{"op":"imm","n":2},"b":{"op":"imm","n":2}}}}';
        $p1 = $c->pass1($prog);
        $expected = json_decode($t1, true);


        $this->assertEquals($p1, json_decode($t1, true), 'Pass1');

        $t2 = '{"op":"/","a":{"op":"-","a":{"op":"+","a":{"op":"*","a":{"op":"imm","n":6},"b":{"op":"arg","n":0}},"b":{"op":"*","a":{"op":"imm","n":5},"b":{"op":"arg","n":1}}},"b":{"op":"*","a":{"op":"imm","n":3},"b":{"op":"arg","n":2}}},"b":{"op":"imm","n":8}}';
        $p2 = $c->pass2($p1);
        $this->assertEquals($p2, json_decode($t2, true), 'Pass2');

        $p3 = $c->pass3($p2);
        $this->assertEquals(simulate($p3, [4,0,0]), 3, 'prog(4,0,0) == 3');
        $this->assertEquals(simulate($p3, [4,8,0]), 8, 'prog(4,8,0) == 8');
        $this->assertEquals(simulate($p3, [4,8,16]), 2, 'prog(4,8,6) == 2');
    }
}
