<?php

namespace app\widgets;

use yii\base\Widget;
use app\models\Coworker;

class StatisticsWidget extends Widget
{
    public $coworkerId;
    public $year;
    public $month;

    public function init()
    {
        parent::init();

        if ($this->year === null) {
            $this->year = date('Y');
        }
        if ($this->month === null) {
            $this->month = date('n');
        }
    }

    public function run()
    {
        $coworker = Coworker::findOne($this->coworkerId);
        if (!$coworker) {
            return '';
        }

        $startDate = date('Y-m-01', strtotime("{$this->year}-{$this->month}-01"));
        $endDate = date('Y-m-t', strtotime("{$this->year}-{$this->month}-01"));

        $data = [
            'coworker' => $coworker,
            'debitAmount' => $coworker->getDebitAmount($startDate, $endDate),
            'creditAmount' => $coworker->getCreditAmount($startDate, $endDate),
            'debitHours' => $coworker->getDebitHours($startDate, $endDate),
            'creditHours' => $coworker->getCreditHours($startDate, $endDate),
            'orders' => $coworker->getOrdersDetails($startDate, $endDate),
            'year' => $this->year,
            'month' => $this->month,
            'monthName' => date('F', strtotime("{$this->year}-{$this->month}-01")),
        ];

        return $this->render('statistics', $data);
    }
}