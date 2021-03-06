<?php
/**
 * 用户操作的步骤要求的选填项的数据
 */

namespace App\Utils\Pipeline;


interface IActionOption extends IPersistent
{
    public function getAction(): IAction;
    public function getNodeOption(): INodeOption;
    public function getValue(): string;
}