<?php declare(strict_types=1);
namespace Phan\Language\Type;

use Phan\Language\Type;

class NullType extends ScalarType
{
    const NAME = 'null';

    /**
     * @param string $name
     * The name of the type such as 'int' or 'MyClass'
     *
     * @param string $namespace
     * The (optional) namespace of the type such as '\'
     * or '\Phan\Language'.
     *
     * @param UnionType[] $template_parameter_type_list
     * A (possibly empty) list of template parameter types
     *
     * @param bool $is_nullable
     * True if this type can be null, false if it cannot
     * be null.
     */
    protected function __construct(
        string $namespace,
        string $name,
        $template_parameter_type_list,
        bool $is_nullable
    ) {
        parent::__construct(
            $namespace,
            $name,
            $template_parameter_type_list,
            true
        );
    }

    /**
     * @param bool $is_nullable
     * Set to true if the type should be nullable, else pass
     * false
     *
     * @return Type
     * A new type that is a copy of this type but with the
     * given nullability value.
     */
    public function withIsNullable(bool $is_nullable) : Type
    {
        return $this;
    }

    public function __toString() : string
    {
        return $this->name;
    }

}
