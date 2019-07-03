<?php

namespace common\interfaces;

interface iCardsBack extends iCardsFront
{
    public function createRow();
    public function updateRow($id);
    public function deleteRow($id);
}
