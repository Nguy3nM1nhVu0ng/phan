<?php
function f0(): string {}
function f1(): ?string {
    return 42;
}
$v = f1();
function f2(): ?string {
    return null;
}
$v = f1();
function f3(?string $name) {}
f3(null);
f3(42);
f3('string');

/**
 * @param ?int $p
 * @return ?string
 */
function f4($p) { return null; }

/**
 * @param int $p
 * @return ?string
 */
function f5($p) { return 42; }

/** @return ?string */
function f6() { return 'string'; }

/** @return ?string[] */
function f7() { return ['string']; }

/** @return ?string[] */
function f8() { return []; }

/** @return ?string[] */
function f9() { return null; }

/** @return Tuple<int,string> */
function f9() { return null; }

