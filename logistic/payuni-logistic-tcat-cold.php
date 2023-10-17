<?php

class PAYUNi_Logistic_Tcat_Cold extends PAYUNi_Logistic
{
    public function __construct($instance_id = 0)
    {
        $this->id                 = 'PAYUNi_Logistic_Tcat_Cold';
        $this->method_title       = __('統一金流 黑貓宅配 冷藏', 'woocommerce');
        $this->method_description = '';
        parent::__construct($instance_id);
    }
}