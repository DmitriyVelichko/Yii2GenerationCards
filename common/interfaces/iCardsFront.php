<?php

namespace common\interfaces;

interface iCardsFront
{
    public function findLastRows($limit);
}

interface iCardsBack extends iCardsFront
{
    public function create($data);
    public function update($id,$data);
    public function delete($id);
}

