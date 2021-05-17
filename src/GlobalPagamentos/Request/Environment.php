<?php
namespace GlobalPagamentos\Request;

interface Environment
{
    public function getEndpoint(): string;
}
