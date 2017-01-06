<?php declare(strict_types=1);
namespace Phan\Language\Type;

use Phan\Language\Type;

abstract class NativeType extends Type
{
    const NAME = '';

    /**
     * @param bool $is_nullable
     * An optional parameter, which if true returns a
     * nullable instance of this native type
     *
     * @return static
     */
    public static function instance(bool $is_nullable)
    {
        if ($is_nullable) {
            static $nullable_instance = null;

            if (empty($nullable_instance)) {
                $nullable_instance = static::make('\\', static::NAME, [], true);
            }

            return $nullable_instance;
        }

        static $instance = null;

        if (empty($instance)) {
            $instance = static::make('\\', static::NAME, [], false);
        }

        return $instance;
    }

    public function isNativeType() : bool
    {
        return true;
    }

    public function isSelfType() : bool
    {
        return false;
    }

    public function __toString() : string
    {
        // Native types can just use their
        // non-fully-qualified names
        $string = $this->name;

        if ($this->getIsNullable()) {
            $string = '?' . $string;
        }

        return $string;
    }
}
