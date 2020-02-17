<?php
declare(strict_types=1);

namespace Gaming\Common\Bus;

final class ViolationParameter
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var bool|int|float|string
     */
    private $value;

    /**
     * ViolationParameter constructor.
     *
     * @param string                $name
     * @param bool|int|float|string $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return bool|int|float|string
     */
    public function value()
    {
        return $this->value;
    }
}
