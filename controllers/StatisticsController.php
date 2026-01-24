<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use app\models\Coworker;
use app\models\Order;
use app\models\Hours;
use app\models\search\StatisticsSearch;
use app\models\export\StatisticsExport;

/**
 * StatisticsController контролирует отображение статистики
 */
class StatisticsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'export' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * Главная страница статистики
     *
     * @param int|null $year Год
     * @param int|null $month Месяц
     * @return string
     */
    public function actionIndex($year = null, $month = null)
    {
        // Устанавливаем значения по умолчанию
        if ($year === null) {
            $year = date('Y');
        }
        if ($month === null) {
            $month = date('n');
        }

        // Валидация входных данных
        if ($year < 2020 || $year > date('Y') + 1) {
            $year = date('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = date('n');
        }

        // Создаем модель поиска
        $searchModel = new StatisticsSearch();
        $searchModel->year = $year;
        $searchModel->month = $month;

        // Получаем данные для таблицы
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Получаем данные для графика
        $chartData = $this->getChartData($year, $month);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Детальная статистика по сотруднику
     *
     * @param int $id ID сотрудника
     * @param int|null $year Год
     * @param int|null $month Месяц
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id, $year = null, $month = null)
    {
        $model = $this->findCoworker($id);

        // Устанавливаем значения по умолчанию
        if ($year === null) {
            $year = date('Y');
        }
        if ($month === null) {
            $month = date('n');
        }

        // Валидация
        if ($year < 2020 || $year > date('Y') + 1) {
            $year = date('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = date('n');
        }

        // Даты периода
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));
        $monthName = date('F', strtotime("$year-$month-01"));

        // Получаем статистику за период
        $statistics = [
            'debitAmount' => $model->getDebitAmount($startDate, $endDate),
            'creditAmount' => $model->getCreditAmount($startDate, $endDate),
            'debitHours' => $model->getDebitHours($startDate, $endDate),
            'creditHours' => $model->getCreditHours($startDate, $endDate),
            'ordersCount' => $model->getCompletedOrdersCount($startDate, $endDate),
            'reportsCount' => $model->getReportsCount($startDate, $endDate),
        ];

        // Получаем детализацию по заказам
        $orders = $model->getOrdersDetails($startDate, $endDate);
        $ordersDataProvider = new ArrayDataProvider([
            'allModels' => $orders,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => ['id', 'date', 'hours', 'amount'],
                'defaultOrder' => ['date' => SORT_DESC],
            ],
        ]);

        // Получаем данные для дневного графика
        $dailyData = $this->getDailyStatistics($model, $startDate, $endDate);

        return $this->render('view', [
            'model' => $model,                    // Coworker модель
            'statistics' => $statistics,          // Массив со статистикой
            'ordersDataProvider' => $ordersDataProvider, // DataProvider для заказов
            'year' => $year,
            'month' => $month,
            'monthName' => $monthName,
            'dailyData' => $dailyData,            // Данные для дневного графика
        ]);
    }

    /**
     * Годовая статистика по сотруднику
     *
     * @param int $id ID сотрудника
     * @param int|null $year Год
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionYearly($id, $year = null)
    {
        $model = $this->findCoworker($id);

        if ($year === null) {
            $year = date('Y');
        }

        // Валидация года
        if ($year < 2020 || $year > date('Y') + 1) {
            $year = date('Y');
        }

        // Получаем годовую статистику - ВРЕМЕННО создаем статические данные
        $yearlyData = $this->getYearlyStatistics($model, $year);

        // Подготавливаем данные для графика
        $monthlyDataJson = json_encode($yearlyData['monthlyData']);

        return $this->render('yearly', [
            'model' => $model,              // Coworker модель
            'year' => $year,
            'yearlyData' => $yearlyData,    // Годовые данные
            'monthlyDataJson' => $monthlyDataJson,
        ]);
    }

    /**
     * Вспомогательный метод для получения годовой статистики
     */
    protected function getYearlyStatistics($coworker, $year)
    {
        $yearlyData = [
            'year' => $year,
            'totalDebitAmount' => 0,
            'totalCreditAmount' => 0,
            'totalDebitHours' => 0,
            'totalCreditHours' => 0,
            'totalOrders' => 0,
            'totalReports' => 0,
            'monthlyData' => [],
        ];

        // Собираем данные по месяцам
        for ($month = 1; $month <= 12; $month++) {
            $startDate = date('Y-m-01', strtotime("$year-$month-01"));
            $endDate = date('Y-m-t', strtotime("$year-$month-01"));

            $monthlyData = [
                'month' => $month,
                'monthName' => date('F', strtotime("$year-$month-01")),
                'debitAmount' => $coworker->getDebitAmount($startDate, $endDate),
                'creditAmount' => $coworker->getCreditAmount($startDate, $endDate),
                'debitHours' => $coworker->getDebitHours($startDate, $endDate),
                'creditHours' => $coworker->getCreditHours($startDate, $endDate),
                'ordersCount' => $coworker->getCompletedOrdersCount($startDate, $endDate),
                'reportsCount' => $coworker->getReportsCount($startDate, $endDate),
            ];

            $yearlyData['monthlyData'][] = $monthlyData;

            // Суммируем общие показатели
            $yearlyData['totalDebitAmount'] += $monthlyData['debitAmount'];
            $yearlyData['totalCreditAmount'] += $monthlyData['creditAmount'];
            $yearlyData['totalDebitHours'] += $monthlyData['debitHours'];
            $yearlyData['totalCreditHours'] += $monthlyData['creditHours'];
            $yearlyData['totalOrders'] += $monthlyData['ordersCount'];
            $yearlyData['totalReports'] += $monthlyData['reportsCount'];
        }

        return $yearlyData;
    }

    /**
     * Страница экспорта статистики
     *
     * @param int|null $year Год
     * @param int|null $month Месяц
     * @param string|null $format Формат экспорта
     * @return string|Response
     */
    public function actionExport($year = null, $month = null, $format = null)
    {
        $model = new StatisticsExport();

        if ($year !== null) {
            $model->year = $year;
        }
        if ($month !== null) {
            $model->month = $month;
        }
        if ($format !== null) {
            $model->format = $format;
        }

        $dataProvider = null;

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            // Если запрос на скачивание файла
            if ($model->format) {
                return $this->downloadExport($model);
            }

            // Иначе показываем превью
            $dataProvider = $this->getExportDataProvider($model->year, $model->month);
        } elseif ($year && $month) {
            // Если переданы параметры в GET
            $dataProvider = $this->getExportDataProvider($year, $month);
        }

        return $this->render('export', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Экспорт статистики по конкретному сотруднику
     *
     * @param int $id ID сотрудника
     * @param int $year Год
     * @param int $month Месяц
     * @param string $format Формат экспорта
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionExportEmployee($id, $year, $month, $format = 'excel')
    {
        $coworker = $this->findCoworker($id);

        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

        // Получаем данные
        $statistics = [
            'Employee' => $coworker->name,
            'Email' => $coworker->email,
            'Period' => date('F Y', strtotime("$year-$month-01")),
            'Paid Hours' => $coworker->getDebitHours($startDate, $endDate),
            'Unpaid Hours' => $coworker->getCreditHours($startDate, $endDate),
            'Total Hours' => $coworker->getDebitHours($startDate, $endDate) + $coworker->getCreditHours($startDate, $endDate),
            'Paid Amount' => $coworker->getDebitAmount($startDate, $endDate),
            'Unpaid Amount' => $coworker->getCreditAmount($startDate, $endDate),
            'Total Amount' => $coworker->getDebitAmount($startDate, $endDate) + $coworker->getCreditAmount($startDate, $endDate),
            'Completed Orders' => $coworker->getCompletedOrdersCount($startDate, $endDate),
            'Reports Submitted' => $coworker->getReportsCount($startDate, $endDate),
        ];

        $orders = $coworker->getOrdersDetails($startDate, $endDate);

        // Генерируем файл в зависимости от формата
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($coworker, $statistics, $orders, $year, $month);
            case 'csv':
                return $this->exportToCsv($coworker, $statistics, $orders, $year, $month);
            case 'pdf':
                return $this->exportToPdf($coworker, $statistics, $orders, $year, $month);
            default:
                throw new NotFoundHttpException('Unsupported export format');
        }
    }

    /**
     * Получение данных для графика (AJAX)
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    public function actionChartData($year, $month)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->getChartData($year, $month);
    }

    /**
     * Обновление статистики в реальном времени (AJAX)
     *
     * @return array
     */
    public function actionLiveUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Здесь можно добавить логику проверки обновлений
        // Например, проверять последнее изменение в базе данных

        return [
            'updated' => true,
            'timestamp' => time(),
            'message' => 'Data is up to date',
        ];
    }

    /**
     * Обновление страницы статистики
     *
     * @param int $year
     * @param int $month
     * @return Response
     */
    public function actionRefresh($year = null, $month = null)
    {
        if ($year && $month) {
            return $this->redirect(['index', 'year' => $year, 'month' => $month]);
        }

        return $this->redirect(['index']);
    }

    /**
     * Скачивание файла экспорта
     *
     * @param StatisticsExport $model
     * @return Response
     */
    protected function downloadExport($model)
    {
        // Получаем данные для экспорта
        $data = $this->getExportData($model->year, $model->month);

        // Генерируем файл
        $filename = "statistics_{$model->year}_{$model->month}." . $model->format;

        switch ($model->format) {
            case 'excel':
                return $this->generateExcel($data, $filename);
            case 'csv':
                return $this->generateCsv($data, $filename);
            case 'pdf':
                return $this->generatePdf($data, $filename);
            default:
                Yii::$app->session->setFlash('error', 'Unsupported export format');
                return $this->redirect(['export']);
        }
    }

    /**
     * Получение данных для экспорта
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    protected function getExportData($year, $month)
    {
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

        $coworkers = Coworker::find()->all();
        $data = [];

        foreach ($coworkers as $coworker) {
            $data[] = [
                'name' => $coworker->name,
                'email' => $coworker->email,
                'debitHours' => $coworker->getDebitHours($startDate, $endDate),
                'creditHours' => $coworker->getCreditHours($startDate, $endDate),
                'debitAmount' => $coworker->getDebitAmount($startDate, $endDate),
                'creditAmount' => $coworker->getCreditAmount($startDate, $endDate),
                'ordersCount' => $coworker->getCompletedOrdersCount($startDate, $endDate),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'monthName' => date('F', strtotime("$year-$month-01")),
            'data' => $data,
            'summary' => $this->calculateSummary($year, $month),
        ];
    }

    /**
     * Получение DataProvider для экспорта
     *
     * @param int $year
     * @param int $month
     * @return ArrayDataProvider
     */
    protected function getExportDataProvider($year, $month)
    {
        $data = $this->getExportData($year, $month);

        return new ArrayDataProvider([
            'allModels' => $data['data'],
            'pagination' => false,
        ]);
    }

    /**
     * Генерация Excel файла
     *
     * @param array $data
     * @param string $filename
     * @return Response
     */
    protected function generateExcel($data, $filename)
    {
        // Используем библиотеку PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Заголовки
        $sheet->setCellValue('A1', 'Statistics Report');
        $sheet->setCellValue('A2', 'Period: ' . $data['monthName'] . ' ' . $data['year']);
        $sheet->setCellValue('A4', 'Employee');
        $sheet->setCellValue('B4', 'Email');
        $sheet->setCellValue('C4', 'Paid Hours');
        $sheet->setCellValue('D4', 'Unpaid Hours');
        $sheet->setCellValue('E4', 'Total Hours');
        $sheet->setCellValue('F4', 'Paid Amount');
        $sheet->setCellValue('G4', 'Unpaid Amount');
        $sheet->setCellValue('H4', 'Total Amount');
        $sheet->setCellValue('I4', 'Completed Orders');

        // Данные
        $row = 5;
        foreach ($data['data'] as $item) {
            $sheet->setCellValue('A' . $row, $item['name']);
            $sheet->setCellValue('B' . $row, $item['email']);
            $sheet->setCellValue('C' . $row, $item['debitHours']);
            $sheet->setCellValue('D' . $row, $item['creditHours']);
            $sheet->setCellValue('E' . $row, $item['debitHours'] + $item['creditHours']);
            $sheet->setCellValue('F' . $row, $item['debitAmount']);
            $sheet->setCellValue('G' . $row, $item['creditAmount']);
            $sheet->setCellValue('H' . $row, $item['debitAmount'] + $item['creditAmount']);
            $sheet->setCellValue('I' . $row, $item['ordersCount']);
            $row++;
        }

        // Сводка
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Summary');
        $sheet->setCellValue('B' . $row, 'Total Employees: ' . $data['summary']['totalCoworkers']);
        $sheet->setCellValue('C' . $row, 'Total Paid Hours: ' . $data['summary']['totalDebitHours']);
        $sheet->setCellValue('D' . $row, 'Total Unpaid Hours: ' . $data['summary']['totalCreditHours']);
        $sheet->setCellValue('E' . $row, 'Total Paid Amount: ' . $data['summary']['totalDebitAmount']);
        $sheet->setCellValue('F' . $row, 'Total Unpaid Amount: ' . $data['summary']['totalCreditAmount']);
        $sheet->setCellValue('G' . $row, 'Total Orders: ' . $data['summary']['totalOrders']);

        // Форматирование
        $sheet->getStyle('A1:I4')->getFont()->setBold(true);
        $sheet->getStyle('F5:H' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        // Авто-ширина колонок
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Создаем writer и отправляем файл
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Генерация CSV файла
     *
     * @param array $data
     * @param string $filename
     * @return Response
     */
    protected function generateCsv($data, $filename)
    {
        $output = fopen('php://output', 'w');

        // Заголовки CSV
        fputcsv($output, [
            'Employee',
            'Email',
            'Paid Hours',
            'Unpaid Hours',
            'Total Hours',
            'Paid Amount',
            'Unpaid Amount',
            'Total Amount',
            'Completed Orders'
        ]);

        // Данные
        foreach ($data['data'] as $item) {
            fputcsv($output, [
                $item['name'],
                $item['email'],
                $item['debitHours'],
                $item['creditHours'],
                $item['debitHours'] + $item['creditHours'],
                $item['debitAmount'],
                $item['creditAmount'],
                $item['debitAmount'] + $item['creditAmount'],
                $item['ordersCount'],
            ]);
        }

        // Сводка
        fputcsv($output, []);
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Total Employees', $data['summary']['totalCoworkers']]);
        fputcsv($output, ['Total Paid Hours', $data['summary']['totalDebitHours']]);
        fputcsv($output, ['Total Unpaid Hours', $data['summary']['totalCreditHours']]);
        fputcsv($output, ['Total Paid Amount', $data['summary']['totalDebitAmount']]);
        fputcsv($output, ['Total Unpaid Amount', $data['summary']['totalCreditAmount']]);
        fputcsv($output, ['Total Orders', $data['summary']['totalOrders']]);

        fclose($output);

        Yii::$app->response->sendContentAsFile(ob_get_clean(), $filename, [
            'mimeType' => 'text/csv',
            'inline' => false,
        ]);

        return Yii::$app->response;
    }

    /**
     * Генерация PDF файла
     *
     * @param array $data
     * @param string $filename
     * @return Response
     */
    protected function generatePdf($data, $filename)
    {
        // Используем библиотеку TCPDF или mPDF
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(Yii::$app->name);
        $pdf->SetAuthor(Yii::$app->name);
        $pdf->SetTitle('Statistics Report');
        $pdf->SetSubject('Statistics Report');

        $pdf->AddPage();

        // Заголовок
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Statistics Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Period: ' . $data['monthName'] . ' ' . $data['year'], 0, 1, 'C');
        $pdf->Ln(10);

        // Таблица
        $pdf->SetFont('helvetica', 'B', 10);
        $header = ['Employee', 'Email', 'Paid Hours', 'Unpaid Hours', 'Paid Amount', 'Orders'];
        $pdf->SetFillColor(240, 240, 240);

        // Заголовки таблицы
        foreach ($header as $col) {
            $pdf->Cell(38, 7, $col, 1, 0, 'C', 1);
        }
        $pdf->Ln();

        // Данные таблицы
        $pdf->SetFont('helvetica', '', 9);
        $fill = false;

        foreach ($data['data'] as $item) {
            $pdf->Cell(38, 6, $item['name'], 'LR', 0, 'L', $fill);
            $pdf->Cell(38, 6, $item['email'], 'LR', 0, 'L', $fill);
            $pdf->Cell(38, 6, number_format($item['debitHours'], 1), 'LR', 0, 'R', $fill);
            $pdf->Cell(38, 6, number_format($item['creditHours'], 1), 'LR', 0, 'R', $fill);
            $pdf->Cell(38, 6, number_format($item['debitAmount'], 2), 'LR', 0, 'R', $fill);
            $pdf->Cell(38, 6, $item['ordersCount'], 'LR', 0, 'R', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }

        $pdf->Cell(array_sum([38, 38, 38, 38, 38, 38]), 0, '', 'T');
        $pdf->Ln(10);

        // Сводка
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Summary', 0, 1);
        $pdf->SetFont('helvetica', '', 11);

        $summaryText = "Total Employees: {$data['summary']['totalCoworkers']}\n" .
            "Total Paid Hours: {$data['summary']['totalDebitHours']}\n" .
            "Total Unpaid Hours: {$data['summary']['totalCreditHours']}\n" .
            "Total Paid Amount: " . number_format($data['summary']['totalDebitAmount'], 2) . "\n" .
            "Total Unpaid Amount: " . number_format($data['summary']['totalCreditAmount'], 2) . "\n" .
            "Total Orders: {$data['summary']['totalOrders']}";

        $pdf->MultiCell(0, 8, $summaryText, 0, 'L');

        // Отправляем PDF
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Экспорт в Excel для сотрудника
     *
     * @param Coworker $coworker
     * @param array $statistics
     * @param array $orders
     * @param int $year
     * @param int $month
     * @return Response
     */
    protected function exportToExcel($coworker, $statistics, $orders, $year, $month)
    {
        $filename = "statistics_{$coworker->id}_{$year}_{$month}.xlsx";

        // Здесь реализация генерации Excel файла для конкретного сотрудника
        // Аналогично generateExcel, но с дополнительными деталями по заказам

        return Yii::$app->response->sendFile($filename);
    }

    /**
     * Экспорт в CSV для сотрудника
     *
     * @param Coworker $coworker
     * @param array $statistics
     * @param array $orders
     * @param int $year
     * @param int $month
     * @return Response
     */
    protected function exportToCsv($coworker, $statistics, $orders, $year, $month)
    {
        $filename = "statistics_{$coworker->id}_{$year}_{$month}.csv";

        // Здесь реализация генерации CSV файла для конкретного сотрудника

        return Yii::$app->response->sendFile($filename);
    }

    /**
     * Экспорт в PDF для сотрудника
     *
     * @param Coworker $coworker
     * @param array $statistics
     * @param array $orders
     * @param int $year
     * @param int $month
     * @return Response
     */
    protected function exportToPdf($coworker, $statistics, $orders, $year, $month)
    {
        $filename = "statistics_{$coworker->id}_{$year}_{$month}.pdf";

        // Здесь реализация генерации PDF файла для конкретного сотрудника

        return Yii::$app->response->sendFile($filename);
    }

    /**
     * Получение данных для графика
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    protected function getChartData($year, $month)
    {
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

        $coworkers = Coworker::find()->all();
        $chartData = [];

        foreach ($coworkers as $coworker) {
            $chartData[$coworker->name] = [
                'paidAmount' => $coworker->getDebitAmount($startDate, $endDate),
                'unpaidAmount' => $coworker->getCreditAmount($startDate, $endDate),
                'paidHours' => $coworker->getDebitHours($startDate, $endDate),
            ];
        }

        return $chartData;
    }

    /**
     * Получение дневной статистики
     *
     * @param Coworker $coworker
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    protected function getDailyStatistics($coworker, $startDate, $endDate)
    {
        // Получаем часы по дням
        $hours = Hours::find()
            ->where(['user_id' => $coworker->id])
            ->andWhere(['>=', 'date', $startDate])
            ->andWhere(['<=', 'date', $endDate])
            ->orderBy(['date' => SORT_ASC])
            ->all();

        $dailyData = [];

        // Группируем по дням
        foreach ($hours as $hour) {
            $day = $hour->date;
            if (!isset($dailyData[$day])) {
                $dailyData[$day] = [
                    'hours' => 0,
                    'amount' => 0,
                ];
            }

            $dailyData[$day]['hours'] += $hour->count;
            if ($hour->is_payed) {
                $dailyData[$day]['amount'] += $hour->debit;
            } else {
                $dailyData[$day]['amount'] += $hour->credit;
            }
        }

        // Заполняем пропущенные дни нулями
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        $result = [];
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $result[$date->format('j')] = isset($dailyData[$day]) ? $dailyData[$day] : [
                'hours' => 0,
                'amount' => 0,
            ];
        }

        return $result;
    }

    /**
     * Расчет сводной статистики
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    protected function calculateSummary($year, $month)
    {
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

        $coworkers = Coworker::find()->all();

        $summary = [
            'totalDebitAmount' => 0,
            'totalCreditAmount' => 0,
            'totalDebitHours' => 0,
            'totalCreditHours' => 0,
            'totalOrders' => 0,
            'totalCoworkers' => count($coworkers),
        ];

        foreach ($coworkers as $coworker) {
            $summary['totalDebitAmount'] += $coworker->getDebitAmount($startDate, $endDate);
            $summary['totalCreditAmount'] += $coworker->getCreditAmount($startDate, $endDate);
            $summary['totalDebitHours'] += $coworker->getDebitHours($startDate, $endDate);
            $summary['totalCreditHours'] += $coworker->getCreditHours($startDate, $endDate);
            $summary['totalOrders'] += $coworker->getCompletedOrdersCount($startDate, $endDate);
        }

        return $summary;
    }

    /**
     * Получение списка доступных лет
     *
     * @return array
     */
    protected function getAvailableYears()
    {
        // Получаем минимальный год из базы данных
        $minYear = Coworker::find()
            ->scalar();

        if (!$minYear) {
            $minYear = 2020;
        }

        $currentYear = date('Y');
        $years = [];

        for ($year = $currentYear; $year >= $minYear; $year--) {
            $years[$year] = $year;
        }

        return $years;
    }

    /**
     * Поиск сотрудника по ID
     *
     * @param int $id
     * @return Coworker
     * @throws NotFoundHttpException
     */
    protected function findCoworker($id)
    {
        $model = Coworker::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $model;
    }
}