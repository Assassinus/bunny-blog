<?php

declare(strict_types=1);

namespace App\Controller;

use Smarty\Exception;

trait NotFoundResponse
{
    /**
     * @throws Exception
     */
    private function renderNotFound(string $message): string
    {
        header('HTTP/1.1 404 Not Found');
        $this->smarty->assign('error_code', 404);
        $this->smarty->assign('message', $message);
        return $this->smarty->fetch('error.tpl');
    }
}
