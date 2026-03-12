<?php

declare(strict_types=1);

namespace App\Controller;

use Smarty\Exception;
use Smarty\Smarty;

abstract class AbstractController implements ControllerInterface
{
    public function __construct(
        protected readonly Smarty $smarty,
    ) {}

    /** @throws Exception */
    protected function render(string $template, array $assigns = []): string
    {
        foreach ($assigns as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        return $this->smarty->fetch($template);
    }
}
