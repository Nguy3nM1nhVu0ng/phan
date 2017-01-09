<?php declare(strict_types=1);
namespace Phan\Analysis;

use Phan\AST\Visitor\Element;
use Phan\AST\Visitor\FlagVisitorImplementation;
use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\UnionType;
use Phan\Language\Type\{
    ArrayType,
    BoolType,
    FloatType,
    IntType,
    StringType
};
use Phan\Issue;
use ast\Node;

class BinaryOperatorFlagVisitor extends FlagVisitorImplementation
{

    /**
     * @var CodeBase
     */
    private $code_base;

    /**
     * @var Context
     */
    private $context;

    /**
     * Create a new BinaryOperatorFlagVisitor
     */
    public function __construct(
        CodeBase $code_base,
        Context $context
    ) {
        $this->code_base = $code_base;
        $this->context = $context;
    }

    /**
     * @param Node $node
     * A node to visit
     */
    public function __invoke(Node $node)
    {
        return (new Element($node))->acceptBinaryFlagVisitor($this);
    }

    /**
     * Default visitor for node kinds that do not have
     * an overriding method
     *
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visit(Node $node) : UnionType
    {
        $left = UnionType::fromNode(
            $this->context,
            $this->code_base,
            $node->children['left']
        );

        $right = UnionType::fromNode(
            $this->context,
            $this->code_base,
            $node->children['right']
        );

        if ($left->isType(ArrayType::instance(false))
            || $right->isType(ArrayType::instance(false))
        ) {
            Issue::maybeEmit(
                $this->code_base,
                $this->context,
                Issue::TypeArrayOperator,
                $node->lineno ?? 0,
                $left, $right
            );

            return new UnionType();
        } elseif ($left->hasType(IntType::instance(false))
            && $right->hasType(IntType::instance(false))
        ) {
            return IntType::instance(false)->asUnionType();
        } elseif ($left->hasType(FloatType::instance(false))
            && $right->hasType(FloatType::instance(false))
        ) {
            return FloatType::instance(false)->asUnionType();
        }

        return new UnionType([
            IntType::instance(false),
            FloatType::instance(false)
        ]);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryBoolAnd(Node $node) : UnionType
    {
        return $this->visitBinaryBool($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryBoolXor(Node $node) : UnionType
    {
        return $this->visitBinaryBool($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryBoolOr(Node $node) : UnionType
    {
        return $this->visitBinaryBool($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryConcat(Node $node) : UnionType
    {
        return StringType::instance(false)->asUnionType();
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    private function visitBinaryOpCommon(Node $node)
    {
            $left = UnionType::fromNode(
                $this->context,
                $this->code_base,
                $node->children['left']
            );

            $right = UnionType::fromNode(
                $this->context,
                $this->code_base,
                $node->children['right']
            );

            if (!$left->genericArrayElementTypes()->isEmpty()
            && $left->nonGenericArrayTypes()->isEmpty()
            && !$right->canCastToUnionType(
                ArrayType::instance(false)->asUnionType()
            )
            ) {
                Issue::maybeEmit(
                    $this->code_base,
                    $this->context,
                    Issue::TypeComparisonFromArray,
                    $node->lineno ?? 0,
                    (string)$right
                );
            } elseif (!$right->genericArrayElementTypes()->isEmpty()
            && $right->nonGenericArrayTypes()->isEmpty()
            && !$left->canCastToUnionType(
                ArrayType::instance(false)->asUnionType()
            )
            ) {
                // and the same for the left side
                Issue::maybeEmit(
                    $this->code_base,
                    $this->context,
                    Issue::TypeComparisonToArray,
                    $node->lineno ?? 0,
                    (string)$left
                );
            }

            return BoolType::instance(false)->asUnionType();
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsIdentical(Node $node) : UnionType
    {
        return $this->visitBinaryOpCommon($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsNotIdentical(Node $node) : UnionType
    {
        return $this->visitBinaryOpCommon($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsEqual(Node $node) : UnionType
    {
        return $this->visitBinaryOpCommon($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsNotEqual(Node $node) : UnionType
    {
        return $this->visitBinaryOpCommon($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsSmaller(Node $node) : UnionType
    {
        return $this->visitBinaryBool($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsSmallerOrEqual(Node $node) : UnionType
    {
        return $this->visitBinaryBool($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsGreater(Node $node) : UnionType
    {
        return $this->visitBinaryBool($node);
    }

    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsGreaterOrEqual(Node $node) : UnionType
    {
        return $this->visitBinaryBool($node);
    }


    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryAdd(Node $node) : UnionType
    {
        $left = UnionType::fromNode(
            $this->context,
            $this->code_base,
            $node->children['left']
        );

        $right = UnionType::fromNode(
            $this->context,
            $this->code_base,
            $node->children['right']
        );

        // fast-track common cases
        if ($left->isType(IntType::instance(false))
            && $right->isType(IntType::instance(false))
        ) {
            return IntType::instance(false)->asUnionType();
        }

        // If both left and right are arrays, then this is array
        // concatenation.
        if ($left->isGenericArray() && $right->isGenericArray()) {
            if ($left->isEqualTo($right)) {
                return $left;
            }

            return ArrayType::instance(false)->asUnionType();
        }

        if (($left->isType(IntType::instance(false))
            || $left->isType(FloatType::instance(false)))
            && ($right->isType(IntType::instance(false))
            || $right->isType(FloatType::instance(false)))
        ) {
            return FloatType::instance(false)->asUnionType();
        }

        $left_is_array = (
            !$left->genericArrayElementTypes()->isEmpty()
            && $left->nonArrayTypes()->isEmpty()
        ) || $left->isType(ArrayType::instance(false));

        $right_is_array = (
            !$right->genericArrayElementTypes()->isEmpty()
            && $right->nonArrayTypes()->isEmpty()
        ) || $right->isType(ArrayType::instance(false));

        if ($left_is_array
            && !$right->canCastToUnionType(
                ArrayType::instance(false)->asUnionType()
            )
        ) {
            Issue::maybeEmit(
                $this->code_base,
                $this->context,
                Issue::TypeInvalidRightOperand,
                $node->lineno ?? 0
            );
            return new UnionType();
        } elseif ($right_is_array
            && !$left->canCastToUnionType(ArrayType::instance(false)->asUnionType())
        ) {
            Issue::maybeEmit(
                $this->code_base,
                $this->context,
                Issue::TypeInvalidLeftOperand,
                $node->lineno ?? 0
            );
            return new UnionType();
        } elseif ($left_is_array || $right_is_array) {
            // If it is a '+' and we know one side is an array
            // and the other is unknown, assume array
            return ArrayType::instance(false)->asUnionType();
        }

        return new UnionType([
            IntType::instance(false),
            FloatType::instance(false)
        ]);
    }

    /**
     * Common visitor for binary boolean operations
     *
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    private function visitBinaryBool(Node $node) : UnionType
    {
        return $this->visitBinaryOpCommon($node);
    }
}
