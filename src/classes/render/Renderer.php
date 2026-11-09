<?php

namespace iutnc\touiteur\render;

interface Renderer {
    public const COMPACT = 1;
    public const DETAIL = 2;

    public function render(int $selector = self::COMPACT): string;
}
