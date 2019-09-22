# SimpleCompiler (Tiny Three-Pass Compiler)

This project is the solution of Kata from CodeWars.

Detailed description: https://www.codewars.com/kata/5265b0885fda8eac5900093b

----

It is a three-pass compiler for a simple programming language into a small assembly language

The programming language has this syntax:

    function   ::= '[' arg-list ']' expression

    arg-list   ::= /* nothing */
                 | variable arg-list

    expression ::= term
                 | expression '+' term
                 | expression '-' term

    term       ::= factor
                 | term '*' factor
                 | term '/' factor

    factor     ::= number
                 | variable
                 | '(' expression ')'
                 
Variables are strings of alphabetic characters. Numbers are strings of decimal digits representing integers. So, for example, a function which computes a^2 + b^2 might look like:

    [ a b ] a*a + b*b
    
A function which computes the average of two numbers might look like:

    [ first second ] (first + second) / 2
