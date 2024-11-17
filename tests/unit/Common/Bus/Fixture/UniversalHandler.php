<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus\Fixture;

final class UniversalHandler
{
    public function __invoke(FirstRequest $request): string
    {
        return __FUNCTION__;
    }

    public function secondAndThird(SecondRequest|ThirdRequest $request): string
    {
        return __FUNCTION__;
    }
}
