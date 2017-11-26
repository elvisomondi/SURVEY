<?php

namespace ls\menu;


interface MenuItemInterface
{
    public function getHref();
    public function getLabel();
    public function getIconClass();
    public function isDivider();
    public function isSmallText();
}

